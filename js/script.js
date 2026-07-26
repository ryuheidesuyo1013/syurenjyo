document.addEventListener("DOMContentLoaded", function() {
    // アコーディオンのセットアップ
    var headers = document.querySelectorAll(".accordion-header");

    headers.forEach(function(header) {
        var button = header.querySelector(".toggle-button");
        var content = header.nextElementSibling;

        // 初期状態の設定
        if (content.classList.contains("open")) {
            button.textContent = "ー";
        } else {
            button.textContent = "＋";
        }

        header.addEventListener("click", function() {
            if (content.classList.contains("open")) {
                content.classList.remove("open");
                button.textContent = "＋";
            } else {
                content.classList.add("open");
                button.textContent = "ー";
            }
        });
    });

    // ハッシュの処理
    var hash = window.location.hash;
    if (hash) {
        var targetElement = document.querySelector(hash);
        if (targetElement) {
            // アコーディオン内の要素であれば開く
            var accordionContent = targetElement.closest(".accordion-content");
            if (accordionContent) {
                // すべてのアコーディオンを閉じる
                document.querySelectorAll(".accordion-content.open").forEach(function(openContent) {
                    openContent.classList.remove("open");
                    var header = openContent.previousElementSibling;
                    var button = header.querySelector(".toggle-button");
                    button.textContent = "＋";
                });

                // 該当するアコーディオンを開く
                var accordionHeader = accordionContent.previousElementSibling;
                var button = accordionHeader.querySelector(".toggle-button");
                if (!accordionContent.classList.contains("open")) {
                    accordionContent.classList.add("open");
                    button.textContent = "ー";
                }
            }
            
            // 該当の要素にスクロール（アコーディオンの内外に関わらず中央表示）
            setTimeout(function() {
                targetElement.scrollIntoView({ behavior: "smooth", block: "center" });
            }, 100);
        }
    }
});