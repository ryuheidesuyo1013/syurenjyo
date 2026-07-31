            </main>

            <footer class="admin-footer">
                <p>
                    &copy; <?= date('Y') ?> 蹴練場 管理画面
                </p>
            </footer>
        </div>
    </div>

    <script>
        'use strict';

        const menuButton = document.querySelector('.admin-menu-button');
        const sidebar = document.querySelector('.admin-sidebar');

        if (menuButton !== null && sidebar !== null) {
            menuButton.addEventListener('click', () => {
                const isOpen = sidebar.classList.toggle('is-open');

                menuButton.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );
            });

            document.addEventListener('click', (event) => {
                const target = event.target;

                if (!(target instanceof Node)) {
                    return;
                }

                const clickedInsideSidebar = sidebar.contains(target);
                const clickedMenuButton = menuButton.contains(target);

                if (
                    window.innerWidth <= 900
                    && !clickedInsideSidebar
                    && !clickedMenuButton
                ) {
                    sidebar.classList.remove('is-open');
                    menuButton.setAttribute('aria-expanded', 'false');
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 900) {
                    sidebar.classList.remove('is-open');
                    menuButton.setAttribute('aria-expanded', 'false');
                }
            });
        }

        const articleForm = document.querySelector('.js-article-form');
        const toolbarElement = document.querySelector('#article-toolbar');
        const editorElement = document.querySelector('#article-editor');
        const contentField = document.querySelector('.js-article-content');

        if (
            articleForm instanceof HTMLFormElement
            && toolbarElement instanceof HTMLElement
            && editorElement instanceof HTMLElement
            && contentField instanceof HTMLTextAreaElement
        ) {
            const quillCssUrl =
                'https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css';

            const quillScriptUrl =
                'https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js';

            const loadQuillStylesheet = () => {
                const existingStylesheet = document.querySelector(
                    'link[data-quill-stylesheet]'
                );

                if (existingStylesheet instanceof HTMLLinkElement) {
                    return Promise.resolve();
                }

                return new Promise((resolve, reject) => {
                    const stylesheet = document.createElement('link');

                    stylesheet.rel = 'stylesheet';
                    stylesheet.href = quillCssUrl;
                    stylesheet.dataset.quillStylesheet = 'true';

                    stylesheet.addEventListener(
                        'load',
                        resolve,
                        { once: true }
                    );

                    stylesheet.addEventListener(
                        'error',
                        reject,
                        { once: true }
                    );

                    document.head.appendChild(stylesheet);
                });
            };

            const loadQuillScript = () => {
                if (typeof window.Quill === 'function') {
                    return Promise.resolve();
                }

                const existingScript = document.querySelector(
                    'script[data-quill-script]'
                );

                if (existingScript instanceof HTMLScriptElement) {
                    return new Promise((resolve, reject) => {
                        existingScript.addEventListener(
                            'load',
                            resolve,
                            { once: true }
                        );

                        existingScript.addEventListener(
                            'error',
                            reject,
                            { once: true }
                        );
                    });
                }

                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');

                    script.src = quillScriptUrl;
                    script.dataset.quillScript = 'true';

                    script.addEventListener(
                        'load',
                        resolve,
                        { once: true }
                    );

                    script.addEventListener(
                        'error',
                        reject,
                        { once: true }
                    );

                    document.body.appendChild(script);
                });
            };

            Promise.all([
                loadQuillStylesheet(),
                loadQuillScript()
            ]).then(() => {
                if (typeof window.Quill !== 'function') {
                    throw new Error('Quillを読み込めませんでした。');
                }

                const quill = new window.Quill(editorElement, {
                    theme: 'snow',
                    placeholder: '記事の本文を入力してください。',
                    modules: {
                        toolbar: toolbarElement
                    }
                });

                const initialContent = contentField.value.trim();

                if (initialContent !== '') {
                    quill.clipboard.dangerouslyPasteHTML(
                        initialContent
                    );
                }

                toolbarElement.hidden = false;
                editorElement.hidden = false;

                contentField.required = false;
                contentField.hidden = true;
                contentField.style.display = 'none';

                articleForm.classList.add('quill-ready');

                articleForm.addEventListener('submit', (event) => {
                    const plainText = quill
                        .getText()
                        .replace(/\s+/g, '')
                        .trim();

                    if (plainText === '') {
                        event.preventDefault();

                        editorElement.classList.add(
                            'article-editor--invalid'
                        );

                        quill.focus();

                        window.alert(
                            '記事の本文を入力してください。'
                        );

                        return;
                    }

                    editorElement.classList.remove(
                        'article-editor--invalid'
                    );

                    contentField.value = quill.root.innerHTML;
                });

                quill.on('text-change', () => {
                    editorElement.classList.remove(
                        'article-editor--invalid'
                    );
                });
            }).catch((error) => {
                console.error(error);

                toolbarElement.hidden = true;
                editorElement.hidden = true;

                contentField.hidden = false;
                contentField.style.display = '';
                contentField.required = true;

                articleForm.classList.remove('quill-ready');
            });
        }
    </script>
</body>
</html>