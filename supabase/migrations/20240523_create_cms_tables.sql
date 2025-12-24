-- Create News table
CREATE TABLE IF NOT EXISTS public.news (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    content TEXT,
    published_date TIMESTAMP WITH TIME ZONE DEFAULT now(),
    category TEXT,
    status TEXT DEFAULT 'draft', -- 'published', 'draft'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Works table
CREATE TABLE IF NOT EXISTS public.works (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    completion_date TIMESTAMP WITH TIME ZONE,
    category TEXT, -- 'residential', 'commercial', etc.
    status TEXT DEFAULT 'draft',
    featured_image TEXT,
    location TEXT,
    images JSONB DEFAULT '[]', -- Gallery images
    client_name TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Services table
CREATE TABLE IF NOT EXISTS public.services (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT,
    detailed_description TEXT,
    features JSONB DEFAULT '[]', -- List of features
    service_image TEXT,
    icon TEXT,
    status TEXT DEFAULT 'active', -- 'active', 'inactive'
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Testimonials table
CREATE TABLE IF NOT EXISTS public.testimonials (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    customer_name TEXT NOT NULL,
    project_type TEXT,
    content TEXT,
    rating INTEGER DEFAULT 5,
    status TEXT DEFAULT 'draft',
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Company Stats table
CREATE TABLE IF NOT EXISTS public.company_stats (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    stat_name TEXT NOT NULL,
    stat_value TEXT NOT NULL,
    stat_unit TEXT,
    icon TEXT,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Partners table
CREATE TABLE IF NOT EXISTS public.partners (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name TEXT NOT NULL,
    logo_url TEXT,
    website_url TEXT,
    status TEXT DEFAULT 'active',
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Representatives table
CREATE TABLE IF NOT EXISTS public.representatives (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    name TEXT NOT NULL,
    position TEXT,
    greeting_title TEXT,
    greeting_content TEXT,
    biography JSONB DEFAULT '{}', -- { "career": [], "education": [] }
    qualifications JSONB DEFAULT '[]',
    photo_url TEXT,
    signature_url TEXT,
    status TEXT DEFAULT 'active',
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Site Settings table
CREATE TABLE IF NOT EXISTS public.site_settings (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    setting_key TEXT UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Company Info table (Single record expected)
CREATE TABLE IF NOT EXISTS public.company_info (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    company_name TEXT,
    representative_title TEXT,
    representative_name TEXT,
    address_postal TEXT,
    address_detail TEXT,
    phone TEXT,
    fax TEXT,
    email TEXT,
    registration_number TEXT,
    business_details JSONB DEFAULT '[]',
    licenses JSONB DEFAULT '[]',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create Company History table
CREATE TABLE IF NOT EXISTS public.company_history (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    year INTEGER NOT NULL,
    month INTEGER,
    event_description TEXT NOT NULL,
    status TEXT DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);
