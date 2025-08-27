import { fetchNews, fetchWorks, fetchStats } from "@/lib/api";

export default async function Home() {
  const [news, works, stats] = await Promise.all([
    fetchNews(5),
    fetchWorks(6),
    fetchStats(),
  ]);

  return (
    <div>
      {/* ヒーロー */}
      <section className="relative h-[600px] md:h-[700px] lg:h-[85vh] overflow-hidden">
        <div className="absolute inset-0 z-0">
          <div className="hero-parallax-bg"></div>
          <div className="absolute inset-0 bg-black/20 z-10"></div>
        </div>
        <div className="container mx-auto relative h-full flex flex-col justify-center z-20 px-4">
          <div className="max-w-3xl">
            <div className="inline-block px-4 py-1 border border-[#A68B5B] bg-black bg-opacity-30 mb-6">
              <span className="text-[#A68B5B] uppercase tracking-wider text-sm md:text-base font-medium block">SINCE 2008</span>
            </div>
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold mb-8 text-white leading-tight">
              伝統と革新で創る、<br/><span className="text-[#A68B5B]">上質な建築</span>の世界
            </h1>
            <p className="text-xl md:text-2xl mb-12 text-white opacity-90 border-l-4 border-[#A68B5B] pl-4">確かな技術と信頼で、皆様の理想を形にします</p>
            <div className="flex flex-col sm:flex-row gap-4">
              <a href="/contact" className="btn-primary px-8 py-4">お問い合わせはこちら</a>
              <a href="/works" className="btn-outline border-white text-white hover:bg-white hover:text-primary px-8 py-4">施工実績を見る</a>
            </div>
          </div>
        </div>
        <div className="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black to-transparent opacity-40 z-10"></div>
        <div className="absolute top-0 left-0 right-0 h-24 bg-gradient-to-b from-black to-transparent opacity-20 z-10"></div>
      </section>

      {/* 実績数カウンター */}
      <section className="py-16 bg-primary text-white">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            {Object.entries(stats || {}).map(([key, value]) => (
              <div key={key} className="p-4">
                <div className="text-4xl font-bold">{value}</div>
                <div className="mt-2 opacity-80 text-sm">{key}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* お知らせ */}
      <section className="section bg-white">
        <div className="container mx-auto px-4">
          <h2 className="section-title">お知らせ</h2>
          <div className="max-w-4xl mx-auto">
            <ul className="divide-y divide-gray-100">
              {news.map((n, idx) => (
                <li key={(n.id ?? idx).toString()} className="block p-6">
                  <div className="flex justify-between items-center">
                    <div>
                      <div className="text-lg font-semibold text-gray-900">{n.title}</div>
                      {n.excerpt && <p className="text-gray-600 mt-1">{n.excerpt}</p>}
                    </div>
                    <div className="text-sm text-gray-500 ml-4 whitespace-nowrap">
                      {n.published_date ? new Date(n.published_date).toLocaleDateString("ja-JP") : ""}
                    </div>
                  </div>
                </li>
              ))}
            </ul>
          </div>
          <div className="text-center mt-10">
            <a href="/news" className="btn-outline">お知らせ一覧へ</a>
          </div>
        </div>
      </section>

      {/* 施工実績（カード） */}
      <section className="section bg-white">
        <div className="container mx-auto px-4">
          <h2 className="section-title">施工実績</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-10">
            {works.map((w, idx) => (
              <div key={(w.id ?? idx).toString()} className="card group">
                <div className="relative overflow-hidden">
                  <img src={w.featured_image || "/img/works_01.jpg"} alt={w.title} className="w-full h-64 object-cover transition-transform duration-700 group-hover:scale-110" />
                  <div className="absolute top-0 right-0 bg-secondary text-white px-4 py-2 text-sm uppercase tracking-wider font-medium">{w.category}</div>
                  <div className="absolute inset-0 bg-primary bg-opacity-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <a href="/works" className="btn-secondary px-4 py-2 text-sm">詳細を見る</a>
                  </div>
                </div>
                <div className="p-6">
                  <span className="text-xs uppercase tracking-wider text-secondary mb-2 block">{w.category}</span>
                  <h3 className="text-xl font-bold mb-2">{w.title}</h3>
                  {w.description && <p className="text-gray-600 mb-4">{w.description}</p>}
                </div>
              </div>
            ))}
          </div>
          <div className="text-center mt-16">
            <a href="/works" className="btn-outline">全ての施工事例を見る</a>
          </div>
        </div>
      </section>
    </div>
  );
}
