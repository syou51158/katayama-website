export type ApiResult<T> = { data: T } | T;

const API_BASE = process.env.NEXT_PUBLIC_API_BASE || "http://localhost/katayama-website/api";

async function getJson(path: string, init?: RequestInit): Promise<unknown> {
  const url = `${API_BASE.replace(/\/$/, "")}/${path.replace(/^\//, "")}`;
  const res = await fetch(url, {
    ...init,
    headers: {
      Accept: "application/json",
      ...(init?.headers || {}),
    },
    next: { revalidate: 60 },
  });
  if (!res.ok) {
    throw new Error(`API error ${res.status} ${res.statusText} for ${url}`);
  }
  return res.json() as Promise<unknown>;
}

function isObject(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null;
}

function isEnvelope<T>(value: unknown): value is { data: T } {
  return isObject(value) && Object.prototype.hasOwnProperty.call(value, "data");
}

function asArray<T>(value: unknown): T[] {
  if (isEnvelope<T[]>(value)) {
    const v = (value as { data: unknown }).data;
    if (Array.isArray(v)) return v as T[];
  }
  if (Array.isArray(value)) return value as T[];
  return [] as T[];
}

function asRecord(value: unknown): Record<string, unknown> {
  if (isEnvelope<Record<string, unknown>>(value)) {
    const v = (value as { data: unknown }).data;
    if (isObject(v)) return v as Record<string, unknown>;
  }
  if (isObject(value)) return value as Record<string, unknown>;
  return {} as Record<string, unknown>;
}

// 型は既存APIの構造に寄せて柔軟に扱う
export type NewsItem = {
  id?: string | number;
  title: string;
  excerpt?: string;
  content?: string;
  category?: string;
  published_date?: string;
  created_at?: string;
  updated_at?: string;
};

export type WorkItem = {
  id?: string | number;
  title: string;
  description?: string;
  category?: string;
  featured_image?: string;
  location?: string;
  completion_date?: string;
  status?: string;
};

export type ServiceItem = {
  id?: string | number;
  name: string;
  description?: string;
  category?: string;
};

export type TestimonialItem = {
  id?: string | number;
  name?: string;
  message: string;
};

export type Stats = Record<string, number>;

export async function fetchNews(limit = 6): Promise<NewsItem[]> {
  const data = await getJson("supabase-news.php");
  const arr = asArray<NewsItem>(data);
  return arr.slice(0, limit);
}

export async function fetchWorks(limit = 6): Promise<WorkItem[]> {
  const data = await getJson("supabase-works.php");
  const arr = asArray<WorkItem>(data);
  return arr.slice(0, limit);
}

export async function fetchServices(): Promise<ServiceItem[]> {
  const data = await getJson("supabase-services.php");
  return asArray<ServiceItem>(data);
}

export async function fetchTestimonials(limit = 6): Promise<TestimonialItem[]> {
  const data = await getJson("supabase-testimonials.php");
  const arr = asArray<TestimonialItem>(data);
  return arr.slice(0, limit);
}

export async function fetchStats(): Promise<Stats> {
  const data = await getJson("supabase-stats.php");
  const arr = asArray<Record<string, unknown>>(data);
  if (arr.length > 0) {
    const out: Record<string, number> = {};
    for (const item of arr) {
      const key = (item.stat_name || item.name || item.title || "stat") as string;
      const rawVal = item.stat_value || item.value || item.count || 0;
      const num = typeof rawVal === "number" ? rawVal : Number(rawVal);
      out[key] = Number.isFinite(num) ? num : 0;
    }
    return out;
  }
  return asRecord(data) as Stats;
}


