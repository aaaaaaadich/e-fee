    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Feenix</h3>
                    <p style="color: rgba(255,255,255,0.8);">
                        A streamlined system for uploading and managing fee receipts. 
                        Secure, fast, and efficient.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope" style="margin-right:8px;"></i> help@ku.edu.np</li>
                        <li><i class="fas fa-phone" style="margin-right:8px;"></i> +977-11-661399</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> Kathmandu University. All Rights Reserved.
            </div>
        </div>
    </footer>

    <!-- Smooth Scrolling Script -->
    <script>
    (function() {
        'use strict';
        
        // Performance optimization: Detect scroll events
        let scrollTimer = null;
        let isScrolling = false;
        
        function handleScroll() {
            if (!isScrolling) {
                isScrolling = true;
                document.body.classList.add('scrolling');
            }
            
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                isScrolling = false;
                document.body.classList.remove('scrolling');
            }, 150);
        }
        
        // Use passive listener for better scroll performance
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        // Enhanced smooth scrolling for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            // Get all anchor links that start with #
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            
            anchorLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Skip empty anchors or just "#"
                    if (!href || href === '#' || href === '#!') {
                        return;
                    }
                    
                    // Try to find the target element
                    const targetId = href.substring(1);
                    const targetElement = document.getElementById(targetId);
                    
                    if (targetElement) {
                        e.preventDefault();
                        
                        // Smooth scroll to element
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start',
                            inline: 'nearest'
                        });
                        
                        // Update URL without jumping
                        if (history.pushState) {
                            history.pushState(null, null, href);
                        }
                    }
                });
            });
            
            // Smooth scroll for "Back to Top" functionality
            window.scrollToTop = function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            };
            
            // Smooth page transitions for form submissions (optional enhancement)
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function() {
                    // Smooth scroll to top before form submission for better UX
                    if (window.scrollY > 100) {
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
        
        // Reduce motion for users who prefer it (accessibility)
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (prefersReducedMotion.matches) {
            document.documentElement.style.scrollBehavior = 'auto';
        }
    })();
    </script>
</body>
</html>
