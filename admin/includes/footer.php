    <!-- Bootstrap JS -->
    <script src="resources/js/main.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/oii13cxduct5af15nee77fercq0vsw9lsib6z8iswjjw9otp/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Toggle sidebar
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                
                // Change icon based on state
                const icon = this.querySelector('i');
                if (sidebar.classList.contains('collapsed')) {
                    icon.classList.remove('bi-list');
                    icon.classList.add('bi-x');
                } else {
                    icon.classList.remove('bi-x');
                    icon.classList.add('bi-list');
                }
            });
            
            // Handle menu toggles for Posts and Users
            const menuToggleBtns = document.querySelectorAll('.menu-toggle-btn');
            
            menuToggleBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const menuId = this.getAttribute('data-menu');
                    const menu = document.getElementById(`${menuId}-menu`);
                    const toggleIcon = this.querySelector('.menu-toggle');
                    
                    // Close other menus
                    document.querySelectorAll('.sub-menu').forEach(m => {
                        if (m.id !== `${menuId}-menu`) {
                            m.classList.remove('show');
                        }
                    });
                    
                    // Reset other toggle icons
                    document.querySelectorAll('.menu-toggle').forEach(icon => {
                        if (icon !== toggleIcon) {
                            icon.classList.remove('rotated');
                        }
                    });
                    
                    // Toggle current menu
                    menu.classList.toggle('show');
                    toggleIcon.classList.toggle('rotated');
                });
            });
            
            // Responsive behavior for mobile
            function handleResize() {
                if (window.innerWidth < 768) {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.remove('expanded');
                } else {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                }
            }
            
            // Initial call and event listener
            handleResize();
            window.addEventListener('resize', handleResize);
        });
//// MOBILE SIDE BAR MENU
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            // Toggle sidebar
            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('active');
                
                // Change icon based on state
                const icon = sidebarToggle.querySelector('i');
                if (sidebar.classList.contains('show')) {
                    icon.classList.remove('bi-list');
                    icon.classList.add('bi-x');
                } else {
                    icon.classList.remove('bi-x');
                    icon.classList.add('bi-list');
                }
            }
            
            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarOverlay.addEventListener('click', toggleSidebar);
            
            // Handle menu toggles for Posts and Users
            const menuToggleBtns = document.querySelectorAll('.menu-toggle-btn');
            
            menuToggleBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const menuId = this.getAttribute('data-menu');
                    const menu = document.getElementById(`${menuId}-menu`);
                    const toggleIcon = this.querySelector('.menu-toggle');
                    
                    // Close other menus
                    document.querySelectorAll('.sub-menu').forEach(m => {
                        if (m.id !== `${menuId}-menu`) {
                            m.classList.remove('show');
                        }
                    });
                    
                    // Reset other toggle icons
                    document.querySelectorAll('.menu-toggle').forEach(icon => {
                        if (icon !== toggleIcon) {
                            icon.classList.remove('rotated');
                        }
                    });
                    
                    // Toggle current menu
                    menu.classList.toggle('show');
                    toggleIcon.classList.toggle('rotated');
                });
            });
            
            // Responsive behavior for mobile
            function handleResize() {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('expanded');
                    
                    // Ensure sidebar is hidden by default on mobile
                    if (!sidebar.classList.contains('show')) {
                        sidebar.style.transform = 'translateX(-100%)';
                    }
                } else {
                    // Reset styles for desktop
                    sidebar.style.transform = '';
                    sidebarOverlay.classList.remove('active');
                    
                    if (sidebar.classList.contains('collapsed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                }
            }
            
            // Initial call and event listener
            handleResize();
            window.addEventListener('resize', handleResize);
        });
    </script>

<script>
    // Show alert message
    function showAlert(message, type) {
        const alertBox = document.getElementById('alertBox');
        alertBox.innerHTML = `
            <div class="alert alert-${type}">
                <span>${message}</span>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        `;
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            if (alertBox.firstChild) {
                alertBox.firstChild.style.display = 'none';
            }
        }, 3000);
    }
    
    // Modal functions
    let deleteId = null;
    let deleteName = null;
    
    function setDeleteId(id, name) {
        deleteId = id;
        deleteName = name;
        document.getElementById('categoryName').textContent = name;
        document.getElementById('deleteModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
        deleteId = null;
        deleteName = null;
    }
    
    function deleteCategory() {
        if (deleteId) {
            window.location.href = `categories.php?delete_category=${deleteId}`;
        }
    }
    
    // Close modal if clicked outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeModal();
        }
    };
    
    // Check if there's a success or error message from URL parameters
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Check for category deletion success/error
        if (urlParams.get('deleted') === 'true') {
            showAlert('Category deleted successfully!', 'success');
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        if (urlParams.get('error') === 'true') {
            showAlert('Error: Could not delete category.', 'error');
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Check for category addition success
        if (urlParams.get('added') === 'true') {
            showAlert('Category added successfully!', 'success');
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Check for category update success
        if (urlParams.get('updated') === 'true') {
            showAlert('Category updated successfully!', 'success');
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Check for edit parameter to populate form
        const editId = urlParams.get('edit');
        if (editId) {
            // You'll need to implement this function to fetch category data
            loadCategoryForEdit(editId);
        }
    });
    
    // Function to load category data for editing (you need to implement this)
    function loadCategoryForEdit(categoryId) {
        // This would typically make an AJAX request to get category data
        console.log('Loading category for edit:', categoryId);
        // Example implementation:
         fetch(`get_category.php?id=${categoryId}`)
             .then(response => response.json())
             .then(data => {
               document.getElementById('cat_title').value = data.cat_title;
                document.getElementById('cat_id').value = data.cat_id;
                // Change form button to say "Update" instead of "Add"
               document.querySelector('button[type="submit"]').textContent = 'Update Category';
             });
    }
</script>
</body>
</html>