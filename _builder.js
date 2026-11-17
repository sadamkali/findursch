const fs = require(`fs`);
const path = require(`path`);
function W(p, c) { const d = path.dirname(p); fs.mkdirSync(d, { recursive: true }); fs.writeFileSync(p, c, `utf8`); console.log(`Wrote: ${p} (${Buffer.byteLength(c, `utf8`)} bytes)`); }

W(`d:\\FInd your school\\findursch\\_test_n.txt`, `hello @extends` + String.fromCharCode(10) + `world`);
