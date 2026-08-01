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

                const toolbar = quill.getModule('toolbar');

                const uploadProgress = document.createElement('div');

                uploadProgress.className = 'article-upload-progress';
                uploadProgress.hidden = true;
                uploadProgress.innerHTML = `
                    <div class="article-upload-progress__track">
                        <div class="article-upload-progress__bar"></div>
                    </div>
                    <span class="article-upload-progress__text">0%</span>
                `;

                editorElement.insertAdjacentElement(
                    'afterend',
                    uploadProgress
                );

                const uploadImage = async (file) => {
                    const csrfTokenField = articleForm.querySelector(
                        'input[name="csrf_token"]'
                    );

                    if (
                        !(csrfTokenField instanceof HTMLInputElement)
                        || csrfTokenField.value === ''
                    ) {
                        throw new Error(
                            'CSRFトークンを取得できませんでした。'
                        );
                    }

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        throw new Error(
                            'JPEG・PNG・WebP形式の画像を選択してください。'
                        );
                    }

                    const maximumFileSize = 5 * 1024 * 1024;

                    if (file.size > maximumFileSize) {
                        throw new Error(
                            '画像は5MB以下にしてください。'
                        );
                    }

                    const formData = new FormData();

                    formData.append(
                        'csrf_token',
                        csrfTokenField.value
                    );

                    formData.append(
                        'image',
                        file
                    );

                    const progressElement = articleForm.querySelector(
                        '.article-upload-progress'
                    );

                    const progressBar = articleForm.querySelector(
                        '.article-upload-progress__bar'
                    );

                    const progressText = articleForm.querySelector(
                        '.article-upload-progress__text'
                    );

                    articleForm.classList.add(
                        'article-image-uploading'
                    );

                    if (progressElement instanceof HTMLElement) {
                        progressElement.hidden = false;
                    }

                    if (progressBar instanceof HTMLElement) {
                        progressBar.style.width = '0%';
                    }

                    if (progressText instanceof HTMLElement) {
                        progressText.textContent = '0%';
                    }

                    try {
                        return await new Promise((resolve, reject) => {
                            const request = new XMLHttpRequest();

                            request.open(
                                'POST',
                                'image-upload.php',
                                true
                            );

                            request.withCredentials = true;

                            request.setRequestHeader(
                                'X-Requested-With',
                                'XMLHttpRequest'
                            );

                            request.upload.addEventListener(
                                'progress',
                                (event) => {
                                    if (!event.lengthComputable) {
                                        return;
                                    }

                                    const progress = Math.min(
                                        100,
                                        Math.round(
                                            (event.loaded / event.total) * 100
                                        )
                                    );

                                    if (progressBar instanceof HTMLElement) {
                                        progressBar.style.width =
                                            progress + '%';
                                    }

                                    if (progressText instanceof HTMLElement) {
                                        progressText.textContent =
                                            progress + '%';
                                    }
                                }
                            );

                            request.addEventListener(
                                'load',
                                () => {
                                    let result;

                                    try {
                                        result = JSON.parse(
                                            request.responseText
                                        );
                                    } catch (error) {
                                        reject(
                                            new Error(
                                                'サーバーから正しい応答を受け取れませんでした。'
                                            )
                                        );

                                        return;
                                    }

                                    if (
                                        request.status < 200
                                        || request.status >= 300
                                        || result.success !== true
                                        || typeof result.url !== 'string'
                                    ) {
                                        reject(
                                            new Error(
                                                typeof result.message === 'string'
                                                    ? result.message
                                                    : '画像のアップロードに失敗しました。'
                                            )
                                        );

                                        return;
                                    }

                                    resolve(result.url);
                                }
                            );

                            request.addEventListener(
                                'error',
                                () => {
                                    reject(
                                        new Error(
                                            '通信エラーが発生しました。'
                                        )
                                    );
                                }
                            );

                            request.addEventListener(
                                'abort',
                                () => {
                                    reject(
                                        new Error(
                                            '画像のアップロードを中断しました。'
                                        )
                                    );
                                }
                            );

                            request.send(formData);
                        });
                    } finally {
                        articleForm.classList.remove(
                            'article-image-uploading'
                        );

                        if (progressElement instanceof HTMLElement) {
                            progressElement.hidden = true;
                        }

                        if (progressBar instanceof HTMLElement) {
                            progressBar.style.width = '0%';
                        }

                        if (progressText instanceof HTMLElement) {
                            progressText.textContent = '0%';
                        }
                    }
                };

                const insertImage = (
                    imageUrl,
                    insertIndex = null
                ) => {
                    const currentRange = quill.getSelection(true);

                    const targetIndex = Number.isInteger(insertIndex)
                        ? insertIndex
                        : currentRange?.index ?? quill.getLength();

                    quill.insertEmbed(
                        targetIndex,
                        'image',
                        imageUrl,
                        'user'
                    );

                    quill.setSelection(
                        targetIndex + 1,
                        0,
                        'silent'
                    );
                };

                const handleImageUpload = async (
                    file,
                    insertIndex = null
                ) => {
                    try {
                        const imageUrl = await uploadImage(file);

                        insertImage(
                            imageUrl,
                            insertIndex
                        );
                    } catch (error) {
                        console.error(error);

                        window.alert(
                            error instanceof Error
                                ? error.message
                                : '画像のアップロードに失敗しました。'
                        );
                    }
                };

                const selectAndUploadImage = () => {
                    const fileInput = document.createElement('input');

                    fileInput.type = 'file';
                    fileInput.accept = 'image/jpeg,image/png,image/webp';
                    fileInput.hidden = true;

                    fileInput.addEventListener(
                        'change',
                        async () => {
                            const file = fileInput.files?.[0];

                            fileInput.remove();

                            if (!(file instanceof File)) {
                                return;
                            }

                            await handleImageUpload(file);
                        },
                        { once: true }
                    );

                    document.body.appendChild(fileInput);
                    fileInput.click();
                };

                toolbar.addHandler(
                    'image',
                    selectAndUploadImage
                );

                let dragEnterCount = 0;

                const showDropZone = () => {
                    dragEnterCount += 1;

                    editorElement.classList.add(
                        'article-editor--dragover'
                    );
                };

                const hideDropZone = () => {
                    dragEnterCount = Math.max(
                        0,
                        dragEnterCount - 1
                    );

                    if (dragEnterCount === 0) {
                        editorElement.classList.remove(
                            'article-editor--dragover'
                        );
                    }
                };

                editorElement.addEventListener(
                    'dragenter',
                    (event) => {
                        const dataTransfer = event.dataTransfer;

                        if (
                            dataTransfer === null
                            || !Array.from(dataTransfer.types).includes('Files')
                        ) {
                            return;
                        }

                        event.preventDefault();
                        showDropZone();
                    }
                );

                editorElement.addEventListener(
                    'dragover',
                    (event) => {
                        const dataTransfer = event.dataTransfer;

                        if (
                            dataTransfer === null
                            || !Array.from(dataTransfer.types).includes('Files')
                        ) {
                            return;
                        }

                        event.preventDefault();
                        dataTransfer.dropEffect = 'copy';
                    }
                );

                editorElement.addEventListener(
                    'dragleave',
                    (event) => {
                        event.preventDefault();
                        hideDropZone();
                    }
                );

                editorElement.addEventListener(
                    'drop',
                    async (event) => {
                        event.preventDefault();

                        dragEnterCount = 0;

                        editorElement.classList.remove(
                            'article-editor--dragover'
                        );

                        const dataTransfer = event.dataTransfer;

                        if (dataTransfer === null) {
                            return;
                        }

                        const imageFiles = Array.from(
                            dataTransfer.files
                        ).filter(
                            (file) => file.type.startsWith('image/')
                        );

                        if (imageFiles.length === 0) {
                            window.alert(
                                '画像ファイルをドロップしてください。'
                            );

                            return;
                        }

                        const currentRange = quill.getSelection(true);

                        let insertIndex = currentRange?.index
                            ?? quill.getLength();

                        for (const file of imageFiles) {
                            await handleImageUpload(
                                file,
                                insertIndex
                            );

                            insertIndex += 1;
                        }
                    }
                );

                const editorRoot = editorElement.querySelector(
                    '.ql-editor'
                );

                if (editorRoot instanceof HTMLElement) {
                    editorRoot.addEventListener(
                        'paste',
                        async (event) => {
                            const clipboardData = event.clipboardData;

                            if (clipboardData === null) {
                                return;
                            }

                            const imageFiles = Array.from(
                                clipboardData.items
                            )
                                .filter(
                                    (item) => item.kind === 'file'
                                        && item.type.startsWith('image/')
                                )
                                .map(
                                    (item) => item.getAsFile()
                                )
                                .filter(
                                    (file) => file instanceof File
                                );

                            if (imageFiles.length === 0) {
                                return;
                            }

                            event.preventDefault();

                            const currentRange = quill.getSelection(true);

                            let insertIndex = currentRange?.index
                                ?? quill.getLength();

                            for (const file of imageFiles) {
                                await handleImageUpload(
                                    file,
                                    insertIndex
                                );

                                insertIndex += 1;
                            }
                        }
                    );
                }

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

                    const containsImage = quill.root.querySelector(
                        'img'
                    ) !== null;

                    if (
                        plainText === ''
                        && !containsImage
                    ) {
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
