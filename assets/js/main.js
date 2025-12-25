// メインJavaScript

document.addEventListener('DOMContentLoaded', function () {
  // ヘッダースクロール効果
  const header = document.querySelector('header');
  let lastScrollTop = 0;

  window.addEventListener('scroll', function () {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > 100) {
      header.style.background = 'rgba(255, 255, 255, 0.8)';
      header.classList.add('shadow');
      header.classList.remove('bg-transparent');
    } else {
      header.style.background = 'rgba(255, 255, 255, 1)';
      header.classList.remove('shadow');
    }

    lastScrollTop = scrollTop;
  });

  // ハンバーガーメニュー
  const menuButton = document.getElementById('menu-button');
  const mobileNav = document.getElementById('mobile-nav');
  const closeMenu = document.getElementById('close-menu');

  if (menuButton && mobileNav) {
    menuButton.addEventListener('click', function () {
      if (mobileNav.classList.contains('hidden')) {
        mobileNav.classList.remove('hidden');
      }
      // 少し遅延させてactiveクラスを追加することで、display:noneからの遷移でもアニメーションさせる
      setTimeout(() => {
        mobileNav.classList.add('active');
      }, 10);
      document.body.style.overflow = 'hidden';
    });
  }

  if (closeMenu && mobileNav) {
    closeMenu.addEventListener('click', function () {
      mobileNav.classList.remove('active');
      document.body.style.overflow = '';
    });
  }

  // 画像ライトボックス
  const lightboxImages = document.querySelectorAll('.lightbox-image');
  const lightbox = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxClose = document.getElementById('lightbox-close');

  if (lightboxImages.length && lightbox && lightboxImg) {
    lightboxImages.forEach(image => {
      image.addEventListener('click', function () {
        const imgSrc = this.getAttribute('src');
        const imgAlt = this.getAttribute('alt') || '';

        lightboxImg.setAttribute('src', imgSrc);
        lightboxImg.setAttribute('alt', imgAlt);

        // ライトボックスの表示アニメーション
        lightbox.classList.remove('hidden');
        setTimeout(() => {
          lightboxImg.classList.add('scale-100');
          lightboxImg.classList.remove('scale-95');
        }, 10);

        document.body.style.overflow = 'hidden';
      });
    });
  }

  if (lightboxClose && lightbox) {
    lightboxClose.addEventListener('click', function () {
      closeLightbox();
    });

    // 背景クリックでも閉じる
    lightbox.addEventListener('click', function (e) {
      if (e.target === lightbox) {
        closeLightbox();
      }
    });

    // ESCキーでも閉じる
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) {
        closeLightbox();
      }
    });
  }

  function closeLightbox() {
    lightboxImg.classList.add('scale-95');
    lightboxImg.classList.remove('scale-100');

    setTimeout(() => {
      lightbox.classList.add('hidden');
      document.body.style.overflow = '';
    }, 300);
  }

  // お問い合わせフォームバリデーション
  const contactForm = document.getElementById('contact-form');

  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      let isValid = true;

      // 名前チェック
      const nameInput = document.getElementById('name');
      if (nameInput && nameInput.value.trim() === '') {
        showError(nameInput, 'お名前を入力してください');
        isValid = false;
      } else if (nameInput) {
        removeError(nameInput);
      }

      // メールチェック
      const emailInput = document.getElementById('email');
      if (emailInput) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailInput.value.trim() === '') {
          showError(emailInput, 'メールアドレスを入力してください');
          isValid = false;
        } else if (!emailPattern.test(emailInput.value)) {
          showError(emailInput, '有効なメールアドレスを入力してください');
          isValid = false;
        } else {
          removeError(emailInput);
        }
      }

      // 電話番号チェック
      const phoneInput = document.getElementById('phone');
      if (phoneInput && phoneInput.value.trim() !== '') {
        const phonePattern = /^[0-9\-+\s]+$/;
        if (!phonePattern.test(phoneInput.value)) {
          showError(phoneInput, '有効な電話番号を入力してください');
          isValid = false;
        } else {
          removeError(phoneInput);
        }
      } else if (phoneInput) {
        removeError(phoneInput);
      }

      // お問い合わせ内容チェック
      const messageInput = document.getElementById('message');
      if (messageInput && messageInput.value.trim() === '') {
        showError(messageInput, 'お問い合わせ内容を入力してください');
        isValid = false;
      } else if (messageInput) {
        removeError(messageInput);
      }

      if (!isValid) {
        e.preventDefault();

        // 最初のエラー項目にスクロール
        const firstError = contactForm.querySelector('.error');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      } else {
        // フォーム送信アニメーション（確認画面がない場合）
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.innerHTML = '<span class="spinner"></span> 送信中...';
          submitBtn.disabled = true;
        }
      }
    });

    // エラー表示関数
    function showError(input, message) {
      input.classList.add('error');

      // 既存のエラーメッセージを削除
      const existingError = input.parentNode.querySelector('.error-message');
      if (existingError) {
        existingError.remove();
      }

      // 新しいエラーメッセージを追加
      const errorDiv = document.createElement('div');
      errorDiv.className = 'error-message';
      errorDiv.textContent = message;

      // エラーメッセージをフェードインさせる
      errorDiv.style.opacity = '0';
      input.parentNode.appendChild(errorDiv);

      setTimeout(() => {
        errorDiv.style.opacity = '1';
      }, 10);
    }

    // エラー表示解除関数
    function removeError(input) {
      input.classList.remove('error');

      const existingError = input.parentNode.querySelector('.error-message');
      if (existingError) {
        existingError.style.opacity = '0';

        setTimeout(() => {
          existingError.remove();
        }, 300);
      }
    }

    // 入力フィールドのフォーカス効果
    const formInputs = contactForm.querySelectorAll('.form-input');
    formInputs.forEach(input => {
      input.addEventListener('focus', function () {
        this.parentNode.classList.add('focused');
      });

      input.addEventListener('blur', function () {
        this.parentNode.classList.remove('focused');
      });
    });
  }

  // 施工実績ギャラリースライダー
  const prevButton = document.getElementById('slide-prev');
  const nextButton = document.getElementById('slide-next');
  const slider = document.querySelector('.works-slider');

  if (prevButton && nextButton && slider) {
    const slideWidth = 320; // スライドの幅 + マージン

    prevButton.addEventListener('click', () => {
      slider.scrollBy({ left: -slideWidth, behavior: 'smooth' });
    });

    nextButton.addEventListener('click', () => {
      slider.scrollBy({ left: slideWidth, behavior: 'smooth' });
    });

    // スライダーナビゲーションボタンの表示制御
    function updateSliderNav() {
      const isStart = slider.scrollLeft < 10;
      const isEnd = slider.scrollLeft >= slider.scrollWidth - slider.clientWidth - 10;

      if (isStart) {
        prevButton.classList.add('opacity-50', 'cursor-not-allowed');
      } else {
        prevButton.classList.remove('opacity-50', 'cursor-not-allowed');
      }

      if (isEnd) {
        nextButton.classList.add('opacity-50', 'cursor-not-allowed');
      } else {
        nextButton.classList.remove('opacity-50', 'cursor-not-allowed');
      }
    }

    slider.addEventListener('scroll', updateSliderNav);
    window.addEventListener('resize', updateSliderNav);

    // 初期状態を設定
    updateSliderNav();
  }

  // 数字カウントアップアニメーション
  const counters = document.querySelectorAll('.counter-value');

  if (counters.length) {
    const options = {
      threshold: 0.5
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const counter = entry.target;
          const target = parseInt(counter.getAttribute('data-target'), 10);
          const duration = 2000; // 2秒間
          const stepTime = 50; // 50ミリ秒ごとに更新
          const steps = duration / stepTime;
          const increment = target / steps;
          let current = 0;

          const timer = setInterval(() => {
            current += increment;
            counter.textContent = Math.round(current);

            if (current >= target) {
              counter.textContent = target;
              clearInterval(timer);
            }
          }, stepTime);

          observer.unobserve(counter);
        }
      });
    }, options);

    counters.forEach(counter => {
      observer.observe(counter);
    });
  }

  // パララックス効果
  const parallaxElements = document.querySelectorAll('.parallax');

  if (parallaxElements.length) {
    window.addEventListener('scroll', () => {
      const scrollPosition = window.pageYOffset;

      parallaxElements.forEach(element => {
        const speed = element.getAttribute('data-speed') || 0.5;
        const offset = element.offsetTop;
        const distance = (scrollPosition - offset) * speed;

        element.style.transform = `translateY(${distance}px)`;
      });
    });
  }

  // スムーズスクロール
  const scrollLinks = document.querySelectorAll('a[href^="#"]');

  scrollLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      const href = this.getAttribute('href');

      if (href === '#') return;

      e.preventDefault();

      const targetElement = document.querySelector(href);
      if (targetElement) {
        const headerHeight = document.querySelector('header').offsetHeight;
        const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - headerHeight;

        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });

        // モバイルメニューが開いていたら閉じる
        if (mobileNav && !mobileNav.classList.contains('hidden')) {
          mobileNav.classList.add('hidden');
          document.body.style.overflow = '';
        }
      }
    });
  });

  // 管理者用ツールバーの表示
  if (typeof localStorage !== 'undefined') {
    const isAdmin = localStorage.getItem('isAdmin');
    const loginTime = parseInt(localStorage.getItem('adminLoginTime') || '0', 10);
    const now = new Date().getTime();

    // 24時間経過していたらセッション切れとみなす
    if (isAdmin === 'true' && (now - loginTime) < 24 * 60 * 60 * 1000) {
      const adminBar = document.createElement('div');
      adminBar.className = 'admin-toolbar fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-3 z-50 flex justify-between items-center shadow-lg border-t border-gray-700';
      adminBar.innerHTML = `
        <div class="flex items-center space-x-4">
          <span class="font-bold text-xs bg-secondary text-white px-2 py-1 rounded">管理者モード</span>
          <span class="text-xs text-gray-300">ログイン中</span>
        </div>
        <div class="flex space-x-3">
          <a href="/admin/" class="text-sm bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded transition-colors text-white no-underline flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            管理画面へ
          </a>
        </div>
      `;
      document.body.appendChild(adminBar);

      // フッターが隠れないようにpaddingを追加
      document.body.style.paddingBottom = '60px';
    } else {
      // 期限切れの場合はフラグ削除
      localStorage.removeItem('isAdmin');
      localStorage.removeItem('adminLoginTime');
    }
  }
});