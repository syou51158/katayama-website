create table public.news (
  id uuid primary key default gen_random_uuid(),
  title text not null,
  content text not null,
  excerpt text,
  category text not null,
  featured_image text,
  published_date date not null,
  status text not null,
  created_at timestamptz not null default now()
);
alter table public.news enable row level security;
create policy select_published on public.news for select to anon using (status = 'published');
create index news_published_date_idx on public.news (published_date);
create index news_created_at_idx on public.news (created_at);

create table public.works (
  id uuid primary key default gen_random_uuid(),
  title text not null,
  description text,
  category text not null,
  featured_image text,
  location text,
  completion_date date,
  construction_period text,
  floor_area text,
  status text not null,
  gallery_images text[],
  created_at timestamptz not null default now()
);
alter table public.works enable row level security;
create policy select_published on public.works for select to anon using (status = 'published');
create index works_completion_date_idx on public.works (completion_date);
create index works_created_at_idx on public.works (created_at);

create table public.services (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  description text,
  status text not null,
  sort_order int not null default 0,
  created_at timestamptz not null default now()
);
alter table public.services enable row level security;
create policy select_active on public.services for select to anon using (status = 'active');
create index services_sort_order_idx on public.services (sort_order);
create index services_created_at_idx on public.services (created_at);

create table public.testimonials (
  id uuid primary key default gen_random_uuid(),
  author text,
  content text not null,
  status text not null,
  created_at timestamptz not null default now()
);
alter table public.testimonials enable row level security;
create policy select_active on public.testimonials for select to anon using (status = 'active');
create index testimonials_created_at_idx on public.testimonials (created_at);

create table public.company_stats (
  id uuid primary key default gen_random_uuid(),
  label text not null,
  value text not null,
  status text not null,
  sort_order int not null default 0,
  created_at timestamptz not null default now()
);
alter table public.company_stats enable row level security;
create policy select_active on public.company_stats for select to anon using (status = 'active');
create index company_stats_sort_order_idx on public.company_stats (sort_order);
create index company_stats_created_at_idx on public.company_stats (created_at);

create table public.partners (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  logo_url text,
  status text not null,
  sort_order int not null default 0,
  created_at timestamptz not null default now()
);
alter table public.partners enable row level security;
create policy select_active on public.partners for select to anon using (status = 'active');
create index partners_sort_order_idx on public.partners (sort_order);
create index partners_created_at_idx on public.partners (created_at);

create table public.site_settings (
  setting_key text primary key,
  setting_value text not null
);
alter table public.site_settings enable row level security;
create policy select_all on public.site_settings for select to anon using (true);

