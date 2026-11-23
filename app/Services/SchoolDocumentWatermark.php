<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use ZipArchive;

class SchoolDocumentWatermark
{
    private const LOGO_URL = 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_homepage_logo/93a4ce7e-8573-44a5-8fad-d2b0ffa35fd1.png';
    private const SITE_URL = 'findursch.riotinvestments.space';

    public function createWatermarkedCopy(string $sourcePath, string $extension): string
    {
        $assets = $this->logoAssets();
        $outputPath = tempnam(sys_get_temp_dir(), 'findursch-watermarked-');

        if ($outputPath === false) {
            throw new RuntimeException('A temporary document could not be created.');
        }

        try {
            if ($extension === 'pdf') {
                $this->watermarkPdf($sourcePath, $outputPath, $assets['jpg']);
            } elseif ($extension === 'docx') {
                $this->watermarkDocx($sourcePath, $outputPath, $assets['png']);
            } else {
                throw new RuntimeException('This document format is not supported.');
            }

            return $outputPath;
        } catch (\Throwable $exception) {
            @unlink($outputPath);
            throw $exception;
        }
    }

    private function logoAssets(): array
    {
        $directory = storage_path('app/private/branding');
        File::ensureDirectoryExists($directory);

        $pngPath = $directory.'/findursch-watermark.png';
        $jpgPath = $directory.'/findursch-watermark.jpg';

        if (! is_file($pngPath) || filesize($pngPath) < 100) {
            $response = Http::timeout(15)->retry(1, 300)->get(self::LOGO_URL);
            $response->throw();
            $body = $response->body();

            if (strlen($body) > 5 * 1024 * 1024) {
                throw new RuntimeException('The FindURSCH watermark image is too large.');
            }

            $imageInfo = @getimagesizefromstring($body);
            if (! $imageInfo || ($imageInfo['mime'] ?? '') !== 'image/png') {
                throw new RuntimeException('The FindURSCH watermark image could not be validated.');
            }

            file_put_contents($pngPath, $body, LOCK_EX);
        }

        if (! is_file($jpgPath) || filemtime($jpgPath) < filemtime($pngPath)) {
            $source = @imagecreatefrompng($pngPath);
            if (! $source) {
                throw new RuntimeException('The FindURSCH watermark image could not be opened.');
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $flattened = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($flattened, 255, 255, 255);
            imagefill($flattened, 0, 0, $white);
            imagecopy($flattened, $source, 0, 0, 0, 0, $width, $height);
            imagejpeg($flattened, $jpgPath, 92);
            imagedestroy($source);
            imagedestroy($flattened);
        }

        if (! is_file($jpgPath) || filesize($jpgPath) < 100) {
            throw new RuntimeException('The FindURSCH watermark image could not be prepared.');
        }

        return ['png' => $pngPath, 'jpg' => $jpgPath];
    }

    private function watermarkPdf(string $sourcePath, string $outputPath, string $logoPath): void
    {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($sourcePath);

        if ($pageCount < 1 || $pageCount > 200) {
            throw new RuntimeException('The PDF must contain between 1 and 200 pages.');
        }

        $logoSize = getimagesize($logoPath);
        if (! $logoSize || $logoSize[0] < 1 || $logoSize[1] < 1) {
            throw new RuntimeException('The FindURSCH logo could not be read.');
        }

        foreach (range(1, $pageCount) as $pageNumber) {
            $template = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($template);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);

            $logoWidth = min(24.0, max(16.0, $size['width'] * 0.075));
            $logoHeight = $logoWidth * ($logoSize[1] / $logoSize[0]);
            $logoX = ($size['width'] - $logoWidth) / 2;
            $logoY = max(2.0, $size['height'] - $logoHeight - 9.0);

            $pdf->Image($logoPath, $logoX, $logoY, $logoWidth, $logoHeight);
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(115, 115, 115);
            $pdf->SetXY(8, $size['height'] - 6);
            $pdf->Cell($size['width'] - 16, 3.5, self::SITE_URL, 0, 0, 'C');
        }

        $pdf->Output('F', $outputPath);
    }

    private function watermarkDocx(string $sourcePath, string $outputPath, string $logoPath): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Word document watermarking is not available on this server.');
        }

        if (! copy($sourcePath, $outputPath)) {
            throw new RuntimeException('The Word document could not be prepared.');
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException('The Word document is not a valid DOCX file.');
        }

        try {
            $documentXml = $zip->getFromName('word/document.xml');
            $contentTypesXml = $zip->getFromName('[Content_Types].xml');
            if (! is_string($documentXml) || ! is_string($contentTypesXml)) {
                throw new RuntimeException('The Word document is missing required Office parts.');
            }

            $document = $this->loadXml($documentXml);
            $xpath = new \DOMXPath($document);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            $sections = $xpath->query('//w:sectPr');

            if (! $sections || $sections->length === 0) {
                throw new RuntimeException('The Word document has no section information.');
            }

            $documentRelsXml = $zip->getFromName('word/_rels/document.xml.rels');
            $documentRels = $this->loadRelationships(is_string($documentRelsXml) ? $documentRelsXml : null);
            $documentRelRoot = $documentRels->documentElement;
            $documentRelationships = [];

            foreach ($documentRelRoot->childNodes as $node) {
                if ($node instanceof \DOMElement && $node->localName === 'Relationship') {
                    $documentRelationships[$node->getAttribute('Id')] = $node;
                }
            }

            $headerPaths = [];
            $newHeaderPath = 'word/header-findursch-'.Str::lower(Str::random(8)).'.xml';
            $newHeaderId = $this->uniqueRelationshipId($documentRelRoot);
            $newHeaderCreated = false;

            foreach ($sections as $section) {
                if (! $section instanceof \DOMElement) {
                    continue;
                }

                $defaultReference = null;
                foreach ($section->childNodes as $child) {
                    if ($child instanceof \DOMElement && $child->localName === 'headerReference') {
                        $type = $child->getAttributeNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'type');
                        if ($type === '' || $type === 'default') {
                            $defaultReference = $child;
                            break;
                        }
                    }
                }

                if ($defaultReference) {
                    $relationshipId = $defaultReference->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');
                    $relationship = $documentRelationships[$relationshipId] ?? null;
                    if (! $relationship || ! str_ends_with($relationship->getAttribute('Type'), '/header')) {
                        throw new RuntimeException('A Word document header reference is invalid.');
                    }
                    $headerPath = $this->resolvePartPath('word/document.xml', $relationship->getAttribute('Target'));
                } else {
                    if (! $newHeaderCreated) {
                        $relationship = $documentRels->createElementNS(
                            'http://schemas.openxmlformats.org/package/2006/relationships',
                            'Relationship'
                        );
                        $relationship->setAttribute('Id', $newHeaderId);
                        $relationship->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/header');
                        $relationship->setAttribute('Target', basename($newHeaderPath));
                        $documentRelRoot->appendChild($relationship);
                        $documentRelationships[$newHeaderId] = $relationship;
                        $zip->addFromString($newHeaderPath, $this->newHeaderXml());
                        $newHeaderCreated = true;
                    }

                    $headerReference = $document->createElementNS(
                        'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                        'w:headerReference'
                    );
                    $headerReference->setAttributeNS(
                        'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                        'w:type',
                        'default'
                    );
                    $headerReference->setAttributeNS(
                        'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                        'r:id',
                        $newHeaderId
                    );
                    $section->insertBefore($headerReference, $section->firstChild);
                    $headerPath = $newHeaderPath;
                }

                $headerPaths[$headerPath] = true;
            }

            $imageName = 'findursch-watermark-'.Str::lower(Str::random(8)).'.png';
            $imageTarget = 'media/'.$imageName;
            $zip->addFile($logoPath, 'word/'.$imageTarget);

            foreach (array_keys($headerPaths) as $headerPath) {
                $headerXml = $zip->getFromName($headerPath);
                if (! is_string($headerXml)) {
                    throw new RuntimeException('A Word document header could not be read.');
                }

                $headerRelsPath = dirname($headerPath).'/_rels/'.basename($headerPath).'.rels';
                $headerRelsXml = $zip->getFromName($headerRelsPath);
                $headerRels = $this->loadRelationships(is_string($headerRelsXml) ? $headerRelsXml : null);
                $headerRelRoot = $headerRels->documentElement;
                $imageRelationshipId = $this->uniqueRelationshipId($headerRelRoot);

                $imageRelationship = $headerRels->createElementNS(
                    'http://schemas.openxmlformats.org/package/2006/relationships',
                    'Relationship'
                );
                $imageRelationship->setAttribute('Id', $imageRelationshipId);
                $imageRelationship->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');
                $imageRelationship->setAttribute('Target', $imageTarget);
                $headerRelRoot->appendChild($imageRelationship);

                if (! str_contains($headerXml, 'FindURSCHWatermark')) {
                    if (! str_contains($headerXml, 'xmlns:v=')) {
                        $headerXml = preg_replace('/<w:hdr\b/', '<w:hdr xmlns:v="urn:schemas-microsoft-com:vml"', $headerXml, 1) ?? $headerXml;
                    }
                    if (! str_contains($headerXml, 'xmlns:o=')) {
                        $headerXml = preg_replace('/<w:hdr\b/', '<w:hdr xmlns:o="urn:schemas-microsoft-com:office:office"', $headerXml, 1) ?? $headerXml;
                    }
                    $headerXml = str_replace('</w:hdr>', $this->watermarkHeaderMarkup($imageRelationshipId).'</w:hdr>', $headerXml);
                }

                $zip->addFromString($headerPath, $headerXml);
                $zip->addFromString($headerRelsPath, $headerRels->saveXML());
            }

            $contentTypes = $this->loadXml($contentTypesXml);
            $contentXpath = new \DOMXPath($contentTypes);
            $contentXpath->registerNamespace('ct', 'http://schemas.openxmlformats.org/package/2006/content-types');

            if ($contentXpath->query('/ct:Types/ct:Default[translate(@Extension, "PNG", "png")="png"]')->length === 0) {
                $default = $contentTypes->createElementNS('http://schemas.openxmlformats.org/package/2006/content-types', 'Default');
                $default->setAttribute('Extension', 'png');
                $default->setAttribute('ContentType', 'image/png');
                $contentTypes->documentElement->appendChild($default);
            }

            if ($newHeaderCreated && $contentXpath->query('/ct:Types/ct:Override[@PartName="'.$newHeaderPath.'"]')->length === 0) {
                $override = $contentTypes->createElementNS('http://schemas.openxmlformats.org/package/2006/content-types', 'Override');
                $override->setAttribute('PartName', '/'.$newHeaderPath);
                $override->setAttribute('ContentType', 'application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml');
                $contentTypes->documentElement->appendChild($override);
            }

            $zip->addFromString('word/document.xml', $document->saveXML());
            $zip->addFromString('word/_rels/document.xml.rels', $documentRels->saveXML());
            $zip->addFromString('[Content_Types].xml', $contentTypes->saveXML());
        } finally {
            $zip->close();
        }
    }

    private function newHeaderXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'.
            '<w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '.
            'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '.
            'xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">'.
            '<w:p><w:pPr><w:jc w:val="center"/></w:pPr></w:p></w:hdr>';
    }

    private function watermarkHeaderMarkup(string $relationshipId): string
    {
        return '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:pict>'.
            '<v:shapetype id="_x0000_t75" coordsize="21600,21600" o:spt="75" o:preferrelative="t" '.
            'path="m@4@5l@4@11@9@11@9@5xe"><v:stroke joinstyle="miter"/>'.
            '<v:path o:extrusionok="f" gradientshapeok="t" o:connecttype="rect"/>'.
            '<o:lock v:ext="edit" aspectratio="t"/></v:shapetype>'.
            '<v:shape id="FindURSCHWatermark" type="#_x0000_t75" '.
            'style="position:absolute;margin-left:0;margin-top:0;width:250pt;height:100pt;rotation:315;z-index:-251654144;'.
            'mso-position-horizontal:center;mso-position-horizontal-relative:margin;'.
            'mso-position-vertical:center;mso-position-vertical-relative:margin" stroked="f">'.
            '<v:imagedata r:id="'.$relationshipId.'" o:title="FindURSCH"/>'.
            '<v:fill opacity=".14"/></v:shape></w:pict></w:r></w:p>'.
            '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>'.
            '<w:rPr><w:color w:val="8A8A8A"/><w:sz w:val="12"/></w:rPr>'.
            '<w:t>findursch.riotinvestments.space</w:t></w:r></w:p>';
    }

    private function loadXml(string $xml): \DOMDocument
    {
        $document = new \DOMDocument();
        $document->preserveWhiteSpace = true;
        $document->formatOutput = false;
        if (! $document->loadXML($xml, LIBXML_NONET)) {
            throw new RuntimeException('A Word document XML part is invalid.');
        }
        return $document;
    }

    private function loadRelationships(?string $xml): \DOMDocument
    {
        if (is_string($xml) && trim($xml) !== '') {
            return $this->loadXml($xml);
        }

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->appendChild($document->createElementNS(
            'http://schemas.openxmlformats.org/package/2006/relationships',
            'Relationships'
        ));
        return $document;
    }

    private function uniqueRelationshipId(\DOMElement $root): string
    {
        $existing = [];
        foreach ($root->childNodes as $node) {
            if ($node instanceof \DOMElement && $node->hasAttribute('Id')) {
                $existing[$node->getAttribute('Id')] = true;
            }
        }

        $base = 'rIdFindURSCHWatermark';
        $candidate = $base;
        $index = 1;
        while (isset($existing[$candidate])) {
            $candidate = $base.$index;
            $index++;
        }
        return $candidate;
    }

    private function resolvePartPath(string $basePart, string $target): string
    {
        $path = str_starts_with($target, '/') ? ltrim($target, '/') : dirname($basePart).'/'.$target;
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }
        return implode('/', $segments);
    }
}
