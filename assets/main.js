// assets/main.js

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize AOS (Animate On Scroll)
    AOS.init({
        once: true, // Animasi hanya sekali
        offset: 100, // Trigger offset
        duration: 800,
        easing: 'ease-out-cubic',
    });

    // 2. Navbar Scroll Effect
    const navbarContainer = document.querySelector('#nav-container');
    const navbar = document.querySelector('#navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            // Shrink padding & stronger background
            navbar.classList.remove('py-4');
            navbar.classList.add('py-2');
            
            navbarContainer.classList.add('bg-white/95', 'shadow-md');
            navbarContainer.classList.remove('bg-white/80');
        } else {
            // Original State
            navbar.classList.add('py-4');
            navbar.classList.remove('py-2');
            
            navbarContainer.classList.remove('bg-white/95', 'shadow-md');
            navbarContainer.classList.add('bg-white/80');
        }
    });

    // 3. Counter Animation (Untuk Statistik Hero)
    const counters = document.querySelectorAll('.count-up');
    
    // Intersection Observer untuk trigger animasi saat elemen terlihat
    const observerOptions = { threshold: 0.5 };
    
    const statsObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = +counter.getAttribute('data-target');
                const speed = 200; // Kecepatan animasi
                
                const updateCount = () => {
                    const count = +counter.innerText;
                    const inc = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 20);
                    } else {
                        counter.innerText = target + (target > 100 ? '+' : '');
                    }
                };
                
                updateCount();
                observer.unobserve(counter); // Stop observing
            }
        });
    }, observerOptions);

    counters.forEach(counter => {
        statsObserver.observe(counter);
    });
});

// 4. Filter Menu Logic (Smooth Transition)
function filterMenu(categoryId) {
    const items = document.querySelectorAll(".menu-item");
    const buttons = document.querySelectorAll(".filter-btn");
    
    // Update Button Styles
    // Reset semua tombol
    buttons.forEach(btn => {
        btn.classList.remove("active", "bg-amber-600", "text-white", "border-amber-600");
        btn.classList.add("bg-white", "text-gray-600", "border-transparent");
    });

    // Set tombol aktif (event.target)
    const activeBtn = event.currentTarget;
    activeBtn.classList.remove("bg-white", "text-gray-600", "border-transparent");
    activeBtn.classList.add("active", "bg-amber-600", "text-white", "border-amber-600");

    // Filter Item
    items.forEach(item => {
        const parent = item.parentElement; // Kolom grid
        
        if (categoryId === "all" || item.dataset.category === categoryId) {
            item.style.display = "flex"; // Kembalikan ke flex layout
            // Animasi Masuk
            setTimeout(() => {
                item.style.opacity = "1";
                item.style.transform = "translateY(0) scale(1)";
            }, 50);
        } else {
            // Animasi Keluar
            item.style.opacity = "0";
            item.style.transform = "translateY(20px) scale(0.95)";
            setTimeout(() => {
                item.style.display = "none";
            }, 300); // Tunggu transisi selesai baru hide
        }
    });
    
    // Refresh AOS agar layout tidak berantakan
    setTimeout(() => AOS.refresh(), 400);
}

// 5. Add to Cart Logic (AJAX + Animation)
async function addToCart(menuId, menuName, price) {
    try {
        const response = await fetch("add_to_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `menu_id=${menuId}&menu_name=${encodeURIComponent(menuName)}&price=${price}`,
        });

        const data = await response.json();

        if (data.success) {
            // Update Badge Counter
            const cartCount = document.getElementById("cart-count");
            cartCount.textContent = data.cart_count;
            
            // Efek 'Pop' pada badge
            cartCount.classList.add('scale-150', 'bg-amber-600', 'text-white');
            setTimeout(() => {
                cartCount.classList.remove('scale-150', 'bg-amber-600', 'text-white');
            }, 200);

            showNotification(`${menuName} berhasil ditambahkan!`, "success");
        }
    } catch (error) {
        console.error(error);
        showNotification("Gagal menambahkan item ke keranjang.", "error");
    }
}

// 6. Toast Notification System
function showNotification(message, type = "success") {
    // Hapus notifikasi lama jika ada
    const oldToast = document.querySelector('.toast-notification');
    if(oldToast) oldToast.remove();

    const notification = document.createElement("div");
    
    // Style berdasarkan tipe
    const styleClass = type === "success" 
        ? "bg-white border-l-4 border-amber-500 text-gray-800" 
        : "bg-red-50 border-l-4 border-red-500 text-red-800";
        
    const icon = type === "success" ? "fa-check-circle text-amber-500" : "fa-exclamation-circle text-red-500";

    notification.className = `toast-notification fixed bottom-8 right-8 ${styleClass} px-6 py-4 rounded-lg shadow-2xl z-50 transform translate-y-20 opacity-0 transition-all duration-500 flex items-center gap-4 min-w-[300px]`;
    
    notification.innerHTML = `
        <i class="fas ${icon} text-xl"></i>
        <div>
            <h4 class="font-bold text-sm uppercase tracking-wider mb-0.5">${type === 'success' ? 'Sukses' : 'Error'}</h4>
            <p class="text-sm font-medium">${message}</p>
        </div>
    `;

    document.body.appendChild(notification);

    // Animasi Masuk
    requestAnimationFrame(() => {
        notification.classList.remove('translate-y-20', 'opacity-0');
    });

    // Hilang otomatis setelah 3 detik
    setTimeout(() => {
        notification.classList.add('translate-y-20', 'opacity-0');
        setTimeout(() => notification.remove(), 500);
    }, 3000);
}

// 7. Format Rupiah Helper (Optional usage)
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(angka);
}