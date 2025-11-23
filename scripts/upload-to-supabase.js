const fs = require('fs');
const path = require('path');

const SUPABASE_URL = 'https://kmdoqdsftiorzmjczzyk.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImttZG9xZHNmdGlvcnptamN6enlrIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjI5NTIyODIsImV4cCI6MjA3ODUyODI4Mn0.ZoztxEfNKUX1iMuvV0czfywvyNuxMXY2fhRFeoycBIQ';
const BUCKET = 'website-assets';

const files = [
  { local: 'assets/img/works_01.jpg', remote: 'images/works/community.jpg', type: 'image/jpeg' },
  { local: 'assets/img/works_02.jpg', remote: 'images/works/store.jpg', type: 'image/jpeg' },
  { local: 'assets/img/works_03.jpg', remote: 'images/works/road.jpg', type: 'image/jpeg' },
  { local: 'assets/img/works_04.jpg', remote: 'images/works/park.jpg', type: 'image/jpeg' },
  { local: 'assets/img/works_05.jpg', remote: 'images/works/house.jpg', type: 'image/jpeg' },
  { local: 'assets/img/works_06.jpg', remote: 'images/works/river.jpg', type: 'image/jpeg' },
  { local: 'assets/img/service_reform.jpg', remote: 'images/services/renovation.jpg', type: 'image/jpeg' },
  { local: 'assets/img/service_public.jpg', remote: 'images/services/public.jpg', type: 'image/jpeg' },
  { local: 'assets/img/service_house.jpg', remote: 'images/services/architecture.jpg', type: 'image/jpeg' },
  { local: 'assets/img/service_doboku.jpg', remote: 'images/services/civil.jpg', type: 'image/jpeg' },
];

async function uploadOne({ local, remote, type }) {
  const filePath = path.resolve(__dirname, '..', local);
  if (!fs.existsSync(filePath)) {
    console.error(`[skip] not found: ${local}`);
    return { ok: false, status: 0 };
  }
  const buf = fs.readFileSync(filePath);
  const url = `${SUPABASE_URL}/storage/v1/object/${BUCKET}/${remote}?upsert=true`;

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${SUPABASE_ANON_KEY}`,
      apikey: SUPABASE_ANON_KEY,
      'Content-Type': type || 'application/octet-stream',
    },
    body: buf,
  });
  if (!res.ok) {
    const text = await res.text();
    console.error(`[fail] ${remote} ${res.status}: ${text}`);
    return { ok: false, status: res.status };
  }
  console.log(`[ok] ${remote} -> ${res.status}`);

  const pub = await fetch(`${SUPABASE_URL}/storage/v1/object/public/${BUCKET}/${remote}`, {
    headers: { apikey: SUPABASE_ANON_KEY }
  });
  console.log(`[verify] ${remote} public status: ${pub.status}`);
  return { ok: true, status: res.status };
}

async function main() {
  const results = [];
  for (const f of files) {
    try {
      results.push(await uploadOne(f));
    } catch (e) {
      console.error(`[error] ${f.remote}:`, e);
      results.push({ ok: false, status: -1 });
    }
  }
  const ok = results.filter(r => r.ok).length;
  const ng = results.length - ok;
  console.log(`done: ok=${ok}, ng=${ng}`);
}

main().catch(err => {
  console.error('fatal:', err);
  process.exit(1);
});