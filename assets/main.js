// Echo Cafe - Main JavaScript

// Filter Menu by Category
function filterMenu(categoryId) {
  const items = document.querySelectorAll(".menu-item");
  const buttons = document.querySelectorAll(".filter-btn");

  buttons.forEach((btn) => {
    btn.classList.remove("bg-amber-600", "text-white", "shadow-amber");
    btn.classList.add("bg-white", "text-gray-700");
  });

  event.target.classList.remove("bg-white", "text-gray-700");
  event.target.classList.add("bg-amber-600", "text-white", "shadow-amber");

  items.forEach((item) => {
    if (categoryId === "all" || item.dataset.category === categoryId) {
      item.style.display = "block";
      item.style.animation = "fadeIn 0.5s ease";
    } else {
      item.style.display = "none";
    }
  });
}

// Add to Cart with Animation
async function addToCart(menuId, menuName, price) {
  try {
    const response = await fetch("add_to_cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `menu_id=${menuId}&menu_name=${encodeURIComponent(
        menuName
      )}&price=${price}`,
    });

    const data = await response.json();

    if (data.success) {
      const cartCount = document.getElementById("cart-count");
      cartCount.textContent = data.cart_count;

      // Add pulse animation
      cartCount.classList.add("pulse-badge");
      setTimeout(() => {
        cartCount.classList.remove("pulse-badge");
      }, 1000);

      // Show success notification
      showNotification(`${menuName} berhasil ditambahkan!`, "success");
    }
  } catch (error) {
    showNotification("Terjadi kesalahan!", "error");
  }
}

// Update Cart Quantity
function updateQty(index, change) {
  const input = document.getElementById(`qty-${index}`);
  let newValue = parseInt(input.value) + change;
  if (newValue < 1) newValue = 1;
  input.value = newValue;
}

// Toggle Modal with Animation
function toggleModal(modalId = "modal") {
  const modal = document.getElementById(modalId);
  const modalContent = modal.querySelector("div > div");

  if (modal.classList.contains("hidden")) {
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    setTimeout(() => {
      modalContent.classList.add("modal-enter");
    }, 10);
  } else {
    modalContent.classList.remove("modal-enter");
    setTimeout(() => {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }, 200);
  }
}

// Toggle Delivery Address Field
function toggleAlamat(show) {
  const alamatField = document.getElementById("alamat-field");
  const alamatTextarea = alamatField.querySelector("textarea");

  if (show) {
    alamatField.classList.remove("hidden");
    alamatField.style.animation = "fadeIn 0.3s ease";
    alamatTextarea.required = true;
  } else {
    alamatField.classList.add("hidden");
    alamatTextarea.required = false;
  }
}

// Show Notification
function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  const colors = {
    success: "bg-green-500",
    error: "bg-red-500",
    info: "bg-blue-500",
    warning: "bg-yellow-500",
  };

  notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
  notification.style.transform = "translateX(400px)";
  notification.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas fa-${
              type === "success"
                ? "check-circle"
                : type === "error"
                ? "exclamation-circle"
                : "info-circle"
            }"></i>
            <span>${message}</span>
        </div>
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.transform = "translateX(0)";
  }, 10);

  setTimeout(() => {
    notification.style.transform = "translateX(400px)";
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}

// Smooth Scroll to Section
function scrollToSection(sectionId) {
  const section = document.getElementById(sectionId);
  if (section) {
    section.scrollIntoView({ behavior: "smooth", block: "start" });
  }
}

// Initialize AOS (Animate on Scroll) - if needed
document.addEventListener("DOMContentLoaded", function () {
  // Add fade-in animation to cards
  const cards = document.querySelectorAll(".card-hover");
  cards.forEach((card, index) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(20px)";
    setTimeout(() => {
      card.style.transition = "all 0.5s ease";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, index * 100);
  });

  // Add active class to current page in sidebar
  const currentPage = window.location.pathname.split("/").pop();
  const sidebarLinks = document.querySelectorAll(".sidebar-link");
  sidebarLinks.forEach((link) => {
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active");
    }
  });
});

// Format Currency
function formatRupiah(angka) {
  return "Rp " + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Confirm Delete
function confirmDelete(message = "Yakin ingin menghapus data ini?") {
  return confirm(message);
}

// Print Report
function printReport() {
  window.print();
}

// Export to Excel (if needed in future)
function exportToExcel(tableId, filename = "report") {
  // Placeholder for future implementation
  showNotification("Fitur export akan segera hadir!", "info");
}

// Copy to Clipboard
function copyToClipboard(text) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showNotification("Berhasil disalin!", "success");
    })
    .catch(() => {
      showNotification("Gagal menyalin!", "error");
    });
}

// Debounce Function for Search
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Search Function
const searchItems = debounce(function (searchTerm) {
  const items = document.querySelectorAll(".searchable-item");
  items.forEach((item) => {
    const text = item.textContent.toLowerCase();
    if (text.includes(searchTerm.toLowerCase())) {
      item.style.display = "";
    } else {
      item.style.display = "none";
    }
  });
}, 300);

// Initialize tooltips
function initTooltips() {
  const tooltips = document.querySelectorAll("[data-tooltip]");
  tooltips.forEach((element) => {
    element.addEventListener("mouseenter", function () {
      const tooltip = document.createElement("div");
      tooltip.className = "tooltip";
      tooltip.textContent = this.dataset.tooltip;
      // Add tooltip logic here
    });
  });
}

// Loading State
function showLoading(buttonId) {
  const button = document.getElementById(buttonId);
  if (button) {
    button.disabled = true;
    button.innerHTML =
      '<span class="spinner inline-block mr-2"></span> Loading...';
  }
}

function hideLoading(buttonId, originalText) {
  const button = document.getElementById(buttonId);
  if (button) {
    button.disabled = false;
    button.innerHTML = originalText;
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.getElementById("hero-carousel");
  const dotsContainer = document.getElementById("carousel-dots");
  const slides = carousel.children;
  const totalSlides = slides.length;
  let currentSlide = 0;

  // 1. Create Dots
  for (let i = 0; i < totalSlides; i++) {
    const dot = document.createElement("button");
    dot.classList.add(
      "w-3",
      "h-3",
      "rounded-full",
      "bg-gray-400",
      "hover:bg-amber-600",
      "transition"
    );
    dot.setAttribute("data-slide-to", i);
    dot.addEventListener("click", () => {
      goToSlide(i);
    });
    dotsContainer.appendChild(dot);
  }

  const dots = dotsContainer.children;

  // 2. Function to update carousel position
  function goToSlide(index) {
    currentSlide = index;
    // Scroll to the specific slide (which snaps into view due to Tailwind's snap-x)
    carousel.scrollLeft = slides[index].offsetLeft;
    updateDots();
  }

  // 3. Function to update dot color
  function updateDots() {
    for (let i = 0; i < totalSlides; i++) {
      dots[i].classList.remove("bg-amber-600");
      dots[i].classList.add("bg-gray-400");
    }
    if (dots[currentSlide]) {
      dots[currentSlide].classList.remove("bg-gray-400");
      dots[currentSlide].classList.add("bg-amber-600");
    }
  }

  // 4. Autoplay (Optional)
  const intervalTime = 5000; // 5 seconds
  setInterval(() => {
    let nextSlide = (currentSlide + 1) % totalSlides;
    goToSlide(nextSlide);
  }, intervalTime);

  // 5. Update dots on manual scroll (e.g., swipe) - DEBOUNCED
  let scrollTimeout;
  carousel.addEventListener("scroll", () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
      const scrollLeft = carousel.scrollLeft;
      const slideWidth = carousel.clientWidth;
      // Calculate which slide is closest to the left edge
      currentSlide = Math.round(scrollLeft / slideWidth);
      updateDots();
    }, 100); // Wait 100ms after scrolling stops
  });

  // Initialize first slide and dots
  updateDots();
});
