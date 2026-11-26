export async function onRequest(): Promise<Response> {
  return new Response("FindURSCH Cloudflare Pages Functions are running.", {
    headers: { "Content-Type": "text/plain; charset=utf-8" }
  });
}
