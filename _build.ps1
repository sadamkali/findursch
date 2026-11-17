function W($p, $c) { $d = Split-Path $p -Parent; if (!(Test-Path $d)) { New-Item -ItemType Directory -Force -Path $d | Out-Null }; [System.IO.File]::WriteAllText($p, $c, [System.Text.Encoding]::UTF8); Write-Host ("Wrote: $p (" + $c.Length + " bytes)") }
W 'd:\FInd your school\findursch\app\Livewire\Districts\DistrictSchools.php' @'
<?php
namespace App\Livewire\Districts;
use App\Models\District;
use Livewire\Component;
use Livewire\WithPagination;

class DistrictSchools extends Component
{
    use WithPagination;
    public $district;
    public function mount(District $district) { $this->district = $district; }
    public function render() {
        $schools = $this->district->schools()->active()->verified()
            ->with([''type'', ''curricula''])->paginate(15);
        return view(''livewire.districts.district-schools'', compact(''schools''));
    }
}
'@

