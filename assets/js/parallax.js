// パララックス効果の実装
window.addEventListener('DOMContentLoaded', () => {
  const parallaxBg = document.querySelector('.hero-parallax-bg');
  const parallaxIntensity = 0.3;

  window.addEventListener('scroll', () => {
    const scrollY = window.pageYOffset;
    const translateY = scrollY * parallaxIntensity;
    
    parallaxBg.style.transform = `translateY(${translateY}px)`;
  });

  // モバイル対応
  window.addEventListener('touchmove', () => {
    const scrollY = window.pageYOffset;
    const translateY = scrollY * parallaxIntensity;
    
    requestAnimationFrame(() => {
      parallaxBg.style.transform = `translateY(${translateY}px)`;
    });
  });
});