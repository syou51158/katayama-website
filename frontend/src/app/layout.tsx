import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "片山建設工業",
  description: "伝統と革新で創る、上質な建築の世界",
};

import Link from "next/link";

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body className="antialiased bg-white text-[#2C3241]">
        <header className="bg-white shadow z-50">
          <div className="container mx-auto flex justify-between items-center py-4 px-4">
            <Link href="/" className="flex items-center">
              <img src="/img/logo.svg" alt="片山建設工業" className="h-10" />
            </Link>
            <nav className="hidden md:flex space-x-6">
              <Link href="/" className="nav-link">ホーム</Link>
              <Link href="/services" className="nav-link">事業内容</Link>
              <Link href="/works" className="nav-link">施工実績</Link>
              <Link href="/about" className="nav-link">代表紹介</Link>
              <Link href="/company" className="nav-link">会社概要</Link>
              <Link href="/news" className="nav-link">お知らせ</Link>
              <Link href="/contact" className="nav-link">お問い合わせ</Link>
            </nav>
          </div>
        </header>
        <main className="pt-20 min-h-screen">{children}</main>
        <footer className="bg-gray-900 text-white py-16 mt-16">
          <div className="container mx-auto px-4">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-10">
              <div>
                <img src="/img/logo.svg" alt="片山建設工業" className="h-12 mb-6 brightness-200" />
                <p className="text-gray-400">伝統と革新で創る、<br/>上質な建築の世界。</p>
              </div>
            </div>
            <div className="border-t border-gray-800 mt-12 pt-10 text-center text-sm text-gray-500">
              <p>© 2024 片山建設工業 All Rights Reserved.</p>
            </div>
          </div>
        </footer>
      </body>
    </html>
  );
}
