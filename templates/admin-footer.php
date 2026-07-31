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
    </script>
</body>
</html>