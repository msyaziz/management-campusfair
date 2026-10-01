    </div> <!-- end container -->
    <footer style="padding: 16px 0; border-top: 1px solid #E5E7EB; text-align: center; width: 100%; margin-top: auto;">
        <div style="font-size: 13px; color: #6B7280;">
            &copy; 2026 Campus Fair 2027 Management System. All rights reserved.
        </div>
    </footer>
</main> <!-- end main -->
<?php if (isset($user) && $user): ?>
</div> <!-- end .wrapper -->
<?php endif; ?>

<!-- Bootstrap 5 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar-custom');
    
    if(toggleBtn && sidebar) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay d-md-none';
        document.body.appendChild(overlay);

        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }
});
</script>
</body>
</html>
