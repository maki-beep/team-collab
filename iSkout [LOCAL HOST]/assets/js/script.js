// =============================================================
//  iSkout — script.js (Fully Fixed & Structured)
// =============================================================

let navigationHistoryStack = [];
let userSavedFavoritesRegistry = [];

// Load favorites from localStorage on page load
window.addEventListener('DOMContentLoaded', function() {
  const savedFavorites = localStorage.getItem('userFavorites');
  if (savedFavorites) {
    try {
      userSavedFavoritesRegistry = JSON.parse(savedFavorites);
    } catch (e) {
      console.error('Error loading favorites:', e);
      userSavedFavoritesRegistry = [];
    }
  }
  // Initialize all favorite buttons on the page
  initializeFavoriteIcons();
});

// Initialize favorite icons based on localStorage state
function initializeFavoriteIcons() {
  const heartIcons = document.querySelectorAll('.fav-heart-icon');
  heartIcons.forEach(icon => {
    const listingName = icon.dataset.listing;
    if (listingName && userSavedFavoritesRegistry.includes(listingName)) {
      icon.classList.replace('far', 'fas');
      icon.style.color = '#730000';
    }
  });
}


const ADMIN_CREDENTIALS = {
  username: "admin@pup.edu.ph",
  password: "admin1234",
};
const MOCK_VERIFICATION_CODE = "1234";

// ── Tag Dictionary Matrix ─────────────────────────────────────
const CATEGORY_TAGS_MAP = {
  all: [],
  food: ["Under 100", "Combo Meals", "Sizzling", "Seating Available"],
  study: ["Free WiFi", "Sockets Available", "Airconditioned", "No Time Limit"],
  print: ["Piso Print", "Rush Hardbound", "Book Binding", "Photocopy"],
  rentals: ["Projector", "Extension Cords", "Per-Hour Rate", "Per-Day Rate"],
  supplies: ["Uniforms", "Lanyards", "Engineering Supplies", "Tingi Available"],
  repair: ["Same-Day Fix", "Student Rates", "Android/iOS", "Laptop Specialist"],
};

// ── Mock Listings Backend Database ─────────────────────────────
const MOCK_LISTINGS = [
  {
    id: "kuripot",
    title: "Kape Kuripot",
    category: "food",
    zone: "Outside",
    image:
      "https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&q=80&w=300",
    status: "Open Now",
    hours: "8:00 am – 7:00 pm",
    tags: ["Under 100", "Seating Available"],
    modalId: "modal-kuripot",
  },
  {
    id: "graham",
    title: "Graham Bars",
    category: "food",
    zone: "Inside",
    image:
      "https://images.unsplash.com/photo-1600431521340-491eca880813?auto=format&fit=crop&q=80&w=300",
    status: "Available",
    hours: "Until 4:30 PM",
    tags: ["Under 100", "Combo Meals"],
    modalId: "modal-graham",
  },
  {
    id: "sizzlingstop",
    title: "Sizzling Stop",
    category: "food",
    zone: "Outside",
    image: "",
    status: "Open Now",
    hours: "10:00 am – 7:00 pm",
    tags: ["Sizzling", "Combo Meals", "Under 100"],
    modalId: null,
  },
  {
    id: "comboking",
    title: "Combo King Canteen",
    category: "food",
    zone: "Inside",
    image: "",
    status: "Open Now",
    hours: "7:00 am – 5:00 pm",
    tags: ["Combo Meals", "Seating Available", "Under 100"],
    modalId: null,
  },
  {
    id: "pisoprint",
    title: "Piso Print Station",
    category: "print",
    zone: "Inside",
    image: "",
    status: "Open Now",
    hours: "7:00 am – 6:00 pm",
    tags: ["Piso Print", "Photocopy"],
    modalId: null,
  },
  {
    id: "hardboundexpress",
    title: "Hardbound Express",
    category: "print",
    zone: "Outside",
    image: "",
    status: "Open Now",
    hours: "8:00 am – 5:00 pm",
    tags: ["Rush Hardbound", "Book Binding", "Photocopy"],
    modalId: null,
  },
  {
    id: "studyhub",
    title: "Study Hub — Library Annex",
    category: "study",
    zone: "Inside",
    image: "",
    status: "Open Now",
    hours: "8:00 am – 8:00 pm",
    tags: ["Free WiFi", "Airconditioned", "No Time Limit", "Sockets Available"],
    modalId: null,
  },
  {
    id: "coworklounge",
    title: "Co-Work Lounge",
    category: "study",
    zone: "Outside",
    image: "",
    status: "Open Now",
    hours: "9:00 am – 9:00 pm",
    tags: ["Free WiFi", "Sockets Available", "Airconditioned"],
    modalId: null,
  },
  {
    id: "techfix",
    title: "TechFix PUP",
    category: "repair",
    zone: "Outside",
    image: "",
    status: "Open Now",
    hours: "9:00 am – 5:00 pm",
    tags: ["Android/iOS", "Same-Day Fix", "Student Rates"],
    modalId: null,
  },
  {
    id: "laptopcare",
    title: "LaptopCare Station",
    category: "repair",
    zone: "Outside",
    image: "",
    status: "Open Now",
    hours: "9:00 am – 6:00 pm",
    tags: ["Laptop Specialist", "Same-Day Fix", "Student Rates"],
    modalId: null,
  },
  {
    id: "suppliesstore",
    title: "Iskolar Supplies Co.",
    category: "supplies",
    zone: "Inside",
    image: "",
    status: "Open Now",
    hours: "8:00 am – 5:00 pm",
    tags: ["Uniforms", "Lanyards", "Tingi Available"],
    modalId: null,
  },
  {
    id: "enggsupplies",
    title: "Engg. Supplies Corner",
    category: "supplies",
    zone: "Inside",
    image: "",
    status: "Open Now",
    hours: "8:00 am – 5:00 pm",
    tags: ["Engineering Supplies", "Tingi Available"],
    modalId: null,
  },
  {
    id: "projrentals",
    title: "PUP Projector Rentals",
    category: "rentals",
    zone: "Inside",
    image: "",
    status: "Available",
    hours: "By appointment",
    tags: ["Projector", "Per-Hour Rate", "Extension Cords"],
    modalId: null,
  },
  {
    id: "dailyrentals",
    title: "Daily Gear Rentals",
    category: "rentals",
    zone: "Outside",
    image: "",
    status: "Available",
    hours: "8:00 am – 6:00 pm",
    tags: ["Per-Day Rate", "Extension Cords", "Projector"],
    modalId: null,
  },
];

const CATEGORY_KEY_MAP = {
  "Food Stores": "food",
  "Study & Workspaces": "study",
  Print: "print",
  Rentals: "rentals",
  Supplies: "supplies",
  "Repair & Tech": "repair",
};

// Active filter state variables
let activeSearchCategory = "all";
let activeSearchZone = "All";
let activeSearchTags = new Set();

// =============================================================
//  DOMContentLoaded Initialization
// =============================================================
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".password-toggle").forEach((btn) => {
    btn.addEventListener("click", function () {
      const inp = this.parentElement.querySelector("input");
      const ico = this.querySelector("i");
      if (inp.type === "password") {
        inp.type = "text";
        ico.classList.replace("fa-eye-slash", "fa-eye");
      } else {
        inp.type = "password";
        ico.classList.replace("fa-eye", "fa-eye-slash");
      }
    });
  });

  const check = document.getElementById("register-terms");
  const signUpBtn = document.getElementById("signup-submit-action");
  if (check && signUpBtn) {
    signUpBtn.disabled = !check.checked;
    check.addEventListener("change", () => {
      signUpBtn.disabled = !check.checked;
    });
  }

  const codeBoxes = document.querySelectorAll(".code-box");
  codeBoxes.forEach((box, i) => {
    box.addEventListener("input", function () {
      this.value = this.value.replace(/\D/g, "");
      if (this.value.length === 1 && i < codeBoxes.length - 1)
        codeBoxes[i + 1].focus();
    });
    box.addEventListener("keydown", function (e) {
      if (e.key === "Backspace" && this.value === "" && i > 0)
        codeBoxes[i - 1].focus();
    });
    box.addEventListener("paste", function (e) {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData)
        .getData("text")
        .replace(/\D/g, "")
        .slice(0, 4);
      pasted.split("").forEach((c, idx) => {
        if (codeBoxes[idx]) codeBoxes[idx].value = c;
      });
      const last = Math.min(pasted.length, codeBoxes.length) - 1;
      if (codeBoxes[last]) codeBoxes[last].focus();
    });
  });

  const searchInput = document.getElementById("live-search-input");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      handleLiveSearch(this.value.trim());
    });
  }

  renderHomeSections();

  // Payment Checkbox Logic Initialization
  const individualCheckboxes = document.querySelectorAll(
    ".pay-method-checkbox"
  );
  const allApplicableCheckbox = document.getElementById("pay-method-all");

  if (allApplicableCheckbox) {
    allApplicableCheckbox.addEventListener("change", function () {
      if (this.checked) {
        individualCheckboxes.forEach((cb) => (cb.checked = false));
      }
    });
  }

  individualCheckboxes.forEach((cb) => {
    cb.addEventListener("change", function () {
      const checkedCount = Array.from(individualCheckboxes).filter(
        (c) => c.checked
      ).length;

      if (checkedCount === individualCheckboxes.length) {
        if (allApplicableCheckbox) {
          allApplicableCheckbox.checked = true;
        }
        individualCheckboxes.forEach((c) => (c.checked = false));
      } else if (this.checked && allApplicableCheckbox) {
        allApplicableCheckbox.checked = false;
      }
    });
  });

  // Attach interval picker dynamic enforcers
  ["open", "close"].forEach((type) => {
    const periodSelect = document.getElementById(`pick-${type}-period`);
    const hourSelect = document.getElementById(`pick-${type}-hour`);
    if (periodSelect)
      periodSelect.addEventListener("change", () =>
        enforceTimeRestrictions(type)
      );
    if (hourSelect)
      hourSelect.addEventListener("change", () =>
        enforceTimeRestrictions(type)
      );
  });
});

// =============================================================
//  NAVIGATION FRAMEWORK
// =============================================================
function navigateTo(screenId) {
  const active = document.querySelector(".screen.active");
  if (active && active.id !== screenId) navigationHistoryStack.push(active.id);

  document.querySelectorAll(".screen").forEach((s) => {
    s.classList.remove("active");
    s.style.display = "none";
  });

  const target = document.getElementById(screenId);
  if (target) {
    target.classList.add("active");
    target.style.display = "flex";
    target.style.flexDirection = "column";
    target.style.overflowY = "auto";
  }

  if (screenId !== "screen-filters") {
    const si = document.getElementById("live-search-input");
    if (si) si.value = "";
    const panel = document.getElementById("search-results-panel");
    if (panel) panel.style.display = "none";
    const fc = document.getElementById("filter-screen-content");
    if (fc) fc.style.display = "block";
  }
}

function backToPreviousScreen() {
  if (navigationHistoryStack.length > 0) {
    const prev = navigationHistoryStack.pop();
    document.querySelectorAll(".screen").forEach((s) => {
      s.classList.remove("active");
      s.style.display = "none";
    });
    const target = document.getElementById(prev);
    if (target) {
      target.classList.add("active");
      target.style.display = "flex";
      target.style.flexDirection = "column";
      target.style.overflowY = "auto";
    }
  } else {
    navigateTo("screen-dashboard");
  }
}

function openFilterFromCategory() {
  const catScreen = document.getElementById("screen-food-category");
  const catKey = catScreen.dataset.activeCategory || "all";
  navigateTo("screen-filters");

  const tabs = document.querySelectorAll(
    "#filter-category-tabs .filter-tab-item"
  );
  tabs.forEach((t) => {
    t.classList.remove("active");
    if (
      t.getAttribute("onclick") &&
      t.getAttribute("onclick").includes(`'${catKey}'`)
    ) {
      t.classList.add("active");
      switchFilterCategory(t, catKey);
    }
  });
}

// =============================================================
//  AUTHENTICATION SIMULATION
// =============================================================
function handleAdminLogin() {
  const u = document.getElementById("admin-login-username").value.trim();
  const p = document.getElementById("admin-login-password").value.trim();
  const err = document.getElementById("admin-login-error");
  if (!u || !p) {
    err.style.display = "flex";
    err.innerHTML =
      '<i class="fas fa-exclamation-circle"></i> Please fill in all fields.';
    return;
  }
  if (u === ADMIN_CREDENTIALS.username && p === ADMIN_CREDENTIALS.password) {
    err.style.display = "none";
    alert("Admin portal would open here.");
  } else {
    err.style.display = "flex";
    err.innerHTML =
      '<i class="fas fa-exclamation-circle"></i> Invalid credentials. Please try again.';
  }
}

function resendVerificationCode() {
  const err = document.getElementById("verify-error");
  if (err) err.style.display = "none";
  document.querySelectorAll(".code-box").forEach((b) => (b.value = ""));
  document.querySelectorAll(".code-box")[0].focus();
  const span = document.querySelector(".resend-text span");
  if (span) {
    span.textContent = "Code resent!";
    setTimeout(() => {
      span.textContent = "Resend Code";
    }, 3000);
  }
}

function handleVerify() {
  const boxes = document.querySelectorAll(".code-box");
  const code = Array.from(boxes)
    .map((b) => b.value)
    .join("");
  const err = document.getElementById("verify-error");

  if (code.length < 4) {
    err.style.display = "flex";
    err.innerHTML =
      '<i class="fas fa-exclamation-circle"></i> Please enter all 4 digits.';
    return;
  }

  if (code === MOCK_VERIFICATION_CODE) {
    err.style.display = "none";
    boxes.forEach((b) => (b.value = ""));
    navigateTo("screen-login");
  } else {
    err.style.display = "flex";
    err.innerHTML =
      '<i class="fas fa-exclamation-circle"></i> Incorrect code. Please try again.';
    boxes.forEach((b) => (b.value = ""));
    boxes[0].focus();
  }
}

function renderHomeSections() {
  renderPopupSinta();
  renderNewestListings();
  syncFavoritesUIComponent();
}

function renderPopupSinta() {
  const container = document.getElementById("popup-sinta-container");
  if (!container) return;

  const source = typeof REAL_LISTINGS !== 'undefined' ? REAL_LISTINGS : MOCK_LISTINGS;
  const items = source.slice(0, 3);
  container.innerHTML = "";

  items.forEach((item) => {
    const card = document.createElement("div");
    card.className = "live-card-version";
    card.onclick = () => {
      window.location.href = `listing.php?id=${encodeURIComponent(item.id)}`;
    };

    const imageHTML = item.image
      ? `<div class="card-img-wrapper"><img src="${item.image}" alt="${item.title}" /></div>`
      : `<div class="card-img-wrapper-placeholder"><i class="fas fa-image"></i></div>`;

    card.innerHTML = `
      ${imageHTML}
      <div class="card-version-body">
        <div class="card-version-title">${item.title}</div>
        <div class="card-version-seller">${item.zone} Campus</div>
        <div class="card-version-meta">
          <span class="card-version-price">${item.hours}</span>
          <span class="badge-stock-maroon">LIVE</span>
        </div>
        <div class="card-version-footer">
          <span class="card-version-loc">${item.location || 'Campus area'}</span>
          <span class="badge-live-text">POP-UP</span>
        </div>
      </div>`;

    container.appendChild(card);
  });
}

function renderNewestListings() {
  const container = document.getElementById("newest-listings-container");
  if (!container) return;

  const source = typeof REAL_LISTINGS !== 'undefined' ? REAL_LISTINGS : MOCK_LISTINGS;
  const items = source.slice(0, 6);
  container.innerHTML = "";

  if (items.length === 0) {
    container.innerHTML = `<div class="empty-state-block"><i class="fas fa-store-slash empty-state-icon"></i><p class="empty-state-title">No listings available.</p></div>`;
    return;
  }

  items.forEach((item) => {
    const card = document.createElement("div");
    card.className = "business-catalog-card";
    card.onclick = () => {
      window.location.href = `listing.php?id=${encodeURIComponent(item.id)}`;
    };

    const imgHTML = item.image
      ? `<div class="business-photo"><img src="${item.image}" alt="${item.title}" /></div>`
      : `<div class="business-photo business-photo-placeholder"><i class="fas fa-image"></i></div>`;

    const tags = (item.tags || []).slice(0, 3).map((t) => `<span class="business-tag-item">${t}</span>`).join("");
    card.innerHTML = `${imgHTML}
      <div class="business-details">
        <div class="business-header-row">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="business-title">${item.title}</span>
            <i class="far fa-heart fav-heart-icon" data-listing="${item.title}" onclick="event.stopPropagation();toggleFavoriteInline(this)"></i>
          </div>
          <span class="zone-badge">${item.zone} Campus</span>
        </div>
        <div class="business-status-row">
          <span class="status-indicator open">${item.status}</span>
          <span class="business-hours">| ${item.hours}</span>
        </div>
        <div class="business-tags-container">${tags}</div>
      </div>`;

    container.appendChild(card);
  });
}

// =============================================================
//  CATEGORY VIEWS & FILTER EXECUTION
// =============================================================
function openCategoryPage(categoryName) {
  const key = CATEGORY_KEY_MAP[categoryName] || "food";
  const screen = document.getElementById("screen-food-category");
  screen.dataset.activeCategory = key;

  navigateTo("screen-food-category");

  const header = document.getElementById("current-category-name");
  if (header)
    header.textContent = `Category: ${categoryName.replace(" Stores", "")}`;

  screen.querySelectorAll(".design-pill-row .selection-tag").forEach((b) => {
    if (!b.querySelector("i")) b.classList.remove("active");
  });
  const allBtn = screen.querySelector(
    ".design-pill-row .selection-tag:not(.icon-tag)"
  );
  if (allBtn) allBtn.classList.add("active");

  buildCategoryTagFilters(key);
  renderCategoryListings(key, "All", []);
}

function buildCategoryTagFilters(categoryKey) {
  const wrapper = document.getElementById("category-tag-filters");
  const container = document.getElementById("category-tag-pills");
  const tags = CATEGORY_TAGS_MAP[categoryKey] || [];

  container.innerHTML = "";
  if (tags.length === 0) {
    wrapper.style.display = "none";
    return;
  }

  tags.forEach((tag) => {
    const btn = document.createElement("button");
    btn.className = "selection-tag";
    btn.textContent = tag;
    btn.setAttribute("data-tag-value", tag);
    btn.onclick = function () {
      this.classList.toggle("active");
      applyTagFilterOnCategory();
    };
    container.appendChild(btn);
  });
  wrapper.style.display = "block";
}

function applyTagFilterOnCategory() {
  const screen = document.getElementById("screen-food-category");
  const key = screen.dataset.activeCategory || "food";

  let zone = "All";
  const activeZoneBtn = screen.querySelector(
    "button[onclick^='filterZoneMatrix'].active"
  );
  if (activeZoneBtn) {
    zone = activeZoneBtn.textContent.trim();
  }

  const activeTags = [];
  document
    .querySelectorAll("#category-tag-pills .selection-tag.active")
    .forEach((b) => {
      const dataVal = b.getAttribute("data-tag-value");
      if (dataVal) activeTags.push(dataVal.trim());
    });

  renderCategoryListings(key, zone, activeTags);
}

function renderCategoryListings(categoryKey, zone, selectedTags) {
  const container = document.getElementById("category-listings-container");
  const emptyState = document.getElementById("category-empty-state");

  if (!container) return;

  const source = typeof REAL_LISTINGS !== 'undefined' ? REAL_LISTINGS : MOCK_LISTINGS;
  const filtered = source.filter((item) => {
    if (item.category !== categoryKey) return false;
    if (zone !== "All" && item.zone.toLowerCase() !== zone.toLowerCase())
      return false;

    if (selectedTags && selectedTags.length > 0) {
      const validFilters = selectedTags
        .map((t) => t.trim().toLowerCase())
        .filter((t) => t.length > 0);

      if (validFilters.length > 0) {
        const itemTagsLower = item.tags.map((t) => t.toLowerCase());
        if (
          !validFilters.every((t) => itemTagsLower.includes(t) || t === "all")
        )
          return false;
      }
    }
    return true;
  });

  container.innerHTML = "";

  if (filtered.length === 0) {
    if (emptyState) emptyState.style.display = "flex";
    return;
  }
  if (emptyState) emptyState.style.display = "none";

  filtered.forEach((item) => {
    const card = document.createElement("div");
    card.className = "business-catalog-card-v2";
    if (item.modalId) {
      card.onclick = () => openModal(item.modalId);
      card.style.cursor = "pointer";
    }

    const imgHTML = item.image
      ? `<div class="v2-card-image"><img src="${item.image}" alt="${item.title}" /></div>`
      : `<div class="v2-card-image-placeholder"><i class="fas fa-image"></i></div>`;

    const pills = item.tags
      .map((t) => `<span class="v2-pill shadow-pill">${t}</span>`)
      .join("");

    card.innerHTML = `
      ${imgHTML}
      <div class="v2-card-content">
        <div class="v2-header-line">
          <span class="v2-title">${item.title}</span>
          <div class="v2-actions">
            <i class="far fa-heart fav-heart-icon" data-listing="${item.title}"
               onclick="event.stopPropagation();toggleFavoriteInline(this)"></i>
            ${item.modalId ? '<i class="far fa-comment-alt"></i>' : ""}
          </div>
        </div>
        <p class="v2-status-line">
          <span class="status-open">${item.status}</span> | ${item.hours}
        </p>
        <div class="v2-pills-row">${pills}</div>
      </div>`;

    if (userSavedFavoritesRegistry.includes(item.title)) {
      const h = card.querySelector(".fav-heart-icon");
      if (h) {
        h.classList.replace("far", "fas");
        h.style.color = "#730000";
      }
    }

    container.appendChild(card);
  });
}

function filterZoneMatrix(btn, zone) {
  btn.parentElement.querySelectorAll(".selection-tag").forEach((b) => {
    if (!b.querySelector("i")) b.classList.remove("active");
  });
  btn.classList.add("active");

  const activeTags = [];
  document
    .querySelectorAll("#category-tag-pills .selection-tag.active")
    .forEach((b) => {
      const dataVal = b.getAttribute("data-tag-value");
      if (dataVal) activeTags.push(dataVal.trim());
    });

  const key =
    document.getElementById("screen-food-category").dataset.activeCategory ||
    "food";
  renderCategoryListings(key, zone, activeTags);
}

// =============================================================
//  FAVORITES REGISTRY UTILITIES
// =============================================================
function toggleFavorite(name, btnId) {
  const btn = document.getElementById(btnId);
  const icon = btn ? btn.querySelector("i") : null;
  const idx = userSavedFavoritesRegistry.indexOf(name);
  if (idx === -1) {
    userSavedFavoritesRegistry.push(name);
    if (icon) {
      icon.classList.replace("far", "fas");
      icon.style.color = "#ff6b6b";
    }
  } else {
    userSavedFavoritesRegistry.splice(idx, 1);
    if (icon) {
      icon.classList.replace("fas", "far");
      icon.style.color = "";
    }
  }
  localStorage.setItem('userFavorites', JSON.stringify(userSavedFavoritesRegistry));
  syncFavoritesUIComponent();
}

function toggleFavoriteInline(el) {
  const name = el.dataset.listing;
  const idx = userSavedFavoritesRegistry.indexOf(name);
  if (idx === -1) {
    userSavedFavoritesRegistry.push(name);
    el.classList.replace("far", "fas");
    el.style.color = "#730000";
  } else {
    userSavedFavoritesRegistry.splice(idx, 1);
    el.classList.replace("fas", "far");
    el.style.color = "";
  }
  localStorage.setItem('userFavorites', JSON.stringify(userSavedFavoritesRegistry));
  syncFavoritesUIComponent();
  // Sync all heart icons on page
  initializeFavoriteIcons();
}

function syncFavoritesUIComponent() {
  const el = document.getElementById("favorites-accordion-content");
  if (!el) return;
  if (userSavedFavoritesRegistry.length === 0) {
    el.innerHTML = `<div class="favorites-empty-state">You haven't saved any listings yet.</div>`;
  } else {
    el.innerHTML = `<ul style="list-style:none;display:flex;flex-direction:column;gap:8px;padding-left:5px">
      ${userSavedFavoritesRegistry
        .map(
          (n) =>
            `<li style="font-size:13px;color:var(--text-dark);display:flex;align-items:center;gap:8px">
              <i class="fas fa-bookmark" style="color:var(--maroon-main);font-size:11px"></i>${n}
            </li>`
        )
        .join("")}
    </ul>`;
  }
}

// =============================================================
//  PHOTO UPLOAD & CREATION POST MODULE
// =============================================================
let postUploadedImages = [];

document.addEventListener("DOMContentLoaded", () => {
  const photoUploadInput = document.getElementById("post-photo-upload");
  if (photoUploadInput) {
    photoUploadInput.addEventListener("change", (e) => {
      const files = Array.from(e.target.files);
      if (!files.length) return;

      files.forEach((file) => {
        if (postUploadedImages.length < 5) {
          postUploadedImages.push(URL.createObjectURL(file));
        }
      });

      updatePhotoGrid();
      photoUploadInput.value = "";
    });
  }
});

function handlePhotoBoxClick(index) {
  if (postUploadedImages[index]) {
    postUploadedImages.splice(index, 1);
    updatePhotoGrid();
  } else {
    document.getElementById("post-photo-upload").click();
  }
}

function updatePhotoGrid() {
  const boxes = document.querySelectorAll("#photo-upload-grid .photo-box");

  boxes.forEach((box, index) => {
    if (postUploadedImages[index]) {
      box.innerHTML = `<img src="${postUploadedImages[index]}" alt="Upload preview" />`;
      box.classList.add("has-photo");
      box.style.borderStyle = "solid";
    } else {
      box.classList.remove("has-photo");
      if (index === 0) {
        box.innerHTML = `<i class="fas fa-camera"></i>`;
        box.style.borderStyle = "dashed";
      } else {
        box.innerHTML = `<i class="fas fa-plus"></i>`;
        box.style.borderStyle = "dashed";
      }
    }
  });
}

function handlePostSubmit() {
  const name = document.getElementById("post-name").value.trim();
  const price = document.getElementById("post-price").value.trim();
  const category = document.getElementById("post-category").value;
  const description = document.getElementById("post-description").value.trim();
  const location = document.getElementById("post-location").value.trim();
  const zone = document.getElementById("post-zone").value;
  const stock = document.getElementById("post-stock").value;
  const duration = document.getElementById("form-active-duration").value;
  const contact = document.getElementById("post-contact").value.trim();
  if (!name || !price) {
    alert("Please fill in at least the Product/Service Name and Price.");
    return;
  }
  const fileInput = document.getElementById("post-photo-upload");
  const formData = new FormData();
  formData.append('name', name);
  formData.append('price', price);
  formData.append('category', category);
  formData.append('description', description);
  formData.append('location', location);
  formData.append('zone', zone);
  formData.append('stock', stock);
  formData.append('duration', duration);
  formData.append('contact', contact);
  if (fileInput && fileInput.files.length > 0) {
    formData.append('image', fileInput.files[0]);
  }

  fetch('actions/create_listing.php', {
    method: 'POST',
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === 'success') {
        const toast = document.getElementById("post-success-toast");
        if (toast) {
          toast.style.display = "flex";
          setTimeout(() => {
            toast.style.display = "none";
            resetPostForm();
            window.location.reload();
          }, 1400);
        } else {
          window.location.reload();
        }
      } else {
        alert(data.message || 'Unable to post listing. Please try again.');
      }
    })
    .catch(() => {
      alert('Network error while creating listing. Please try again.');
    });
}

function resetPostForm() {
  [
    "post-name",
    "post-price",
    "post-description",
    "post-contact",
    "custom-time-range-input",
  ].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.value = "";
  });

  const durationSelect = document.getElementById("form-active-duration");
  if (durationSelect) durationSelect.value = "Until supplies last";

  const customDisplayBlock = document.getElementById(
    "custom-interval-display-block"
  );
  if (customDisplayBlock) {
    customDisplayBlock.style.display = "none";
    customDisplayBlock.textContent = "";
  }

  document
    .querySelectorAll(".pay-method-checkbox, #pay-method-all")
    .forEach((cb) => (cb.checked = false));

  postUploadedImages = [];
  updatePhotoGrid();
}

// =============================================================
//  INTERVAL & BUSINESS HOURS MODAL LOGIC
// =============================================================

function enforceTimeRestrictions(type) {
  const periodSelect = document.getElementById(`pick-${type}-period`);
  const hourSelect = document.getElementById(`pick-${type}-hour`);
  const minSelect = document.getElementById(`pick-${type}-min`);

  if (!periodSelect || !hourSelect || !minSelect) return;

  const period = periodSelect.value;

  // Reset disables first
  Array.from(hourSelect.options).forEach((opt) => (opt.disabled = false));
  Array.from(minSelect.options).forEach((opt) => (opt.disabled = false));

  // Enforce logic using disabled attributes so visually invalid times are unclickable
  if (period === "AM") {
    const invalidAM = ["12", "01", "02", "03", "04", "05"];
    Array.from(hourSelect.options).forEach((opt) => {
      if (invalidAM.includes(opt.value)) opt.disabled = true;
    });

    // If current selection is disabled, fallback to valid
    if (invalidAM.includes(hourSelect.value)) {
      hourSelect.value = "06";
    }
  } else {
    const invalidPM = ["09", "10", "11"];
    Array.from(hourSelect.options).forEach((opt) => {
      if (invalidPM.includes(opt.value)) opt.disabled = true;
    });

    // If current selection is disabled, fallback to valid
    if (invalidPM.includes(hourSelect.value)) {
      hourSelect.value = "12";
    }

    // Block 8:30 PM
    if (hourSelect.value === "08") {
      Array.from(minSelect.options).forEach((opt) => {
        if (opt.value === "30") opt.disabled = true;
      });
      if (minSelect.value === "30") {
        minSelect.value = "00";
      }
    }
  }
}

function handleDurationChange(selectElement) {
  const customDisplayBlock = document.getElementById(
    "custom-interval-display-block"
  );
  if (selectElement.value === "Others") {
    openIntervalPickerModal();
  } else {
    if (customDisplayBlock) customDisplayBlock.style.display = "none";
  }
}

function openIntervalPickerModal() {
  const pickerModal = document.getElementById("modal-time-interval-picker");
  if (pickerModal) {
    pickerModal.classList.add("active");
    const dateInput = document.getElementById("pick-sell-date");
    if (dateInput) {
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, "0");
      const dd = String(today.getDate()).padStart(2, "0");
      const minDateString = `${yyyy}-${mm}-${dd}`;
      dateInput.min = minDateString;
      if (!dateInput.value) {
        dateInput.value = minDateString;
      }
    }

    // Force the physical restrictions to apply the moment the modal opens
    enforceTimeRestrictions("open");
    enforceTimeRestrictions("close");
  }
}

function closeIntervalPickerModal() {
  const pickerModal = document.getElementById("modal-time-interval-picker");
  const durationSelect = document.getElementById("form-active-duration");
  const customRangeInput = document.getElementById("custom-time-range-input");

  if (pickerModal) pickerModal.classList.remove("active");
  if (durationSelect && (!customRangeInput || customRangeInput.value === "")) {
    durationSelect.value = "Until supplies last";
  }
}

function saveCustomTimeInterval() {
  const sellDateRaw = document.getElementById("pick-sell-date").value;
  const openHr = document.getElementById("pick-open-hour").value;
  const openMin = document.getElementById("pick-open-min").value;
  const openPeriod = document.getElementById("pick-open-period").value;
  const closeHr = document.getElementById("pick-close-hour").value;
  const closeMin = document.getElementById("pick-close-min").value;
  const closePeriod = document.getElementById("pick-close-period").value;

  if (!sellDateRaw) {
    alert("Please select a selling date.");
    return;
  }

  // --- TIME LIMIT VALIDATION (6 AM - 8 PM) ---
  const parseTime = (hr, min, period) => {
    let h = parseInt(hr);
    if (period === "PM" && h !== 12) h += 12;
    if (period === "AM" && h === 12) h = 0;
    return h + parseInt(min) / 60;
  };

  const openTime = parseTime(openHr, openMin, openPeriod);
  const closeTime = parseTime(closeHr, closeMin, closePeriod);

  if (openTime < 6 || openTime > 20) {
    alert("Opening time must be between 6:00 AM and 8:00 PM (School Hours).");
    return;
  }
  if (closeTime < 6 || closeTime > 20) {
    alert("Closing time must be between 6:00 AM and 8:00 PM (School Hours).");
    return;
  }
  if (openTime >= closeTime) {
    alert("Closing time must be strictly after the opening time.");
    return;
  }
  // ---------------------------------------------

  const dateOptions = { year: "numeric", month: "long", day: "numeric" };
  const formattedDate = new Date(sellDateRaw).toLocaleDateString(
    "en-US",
    dateOptions
  );

  const formattedIntervalString = `${formattedDate} (${openHr}:${openMin} ${openPeriod} - ${closeHr}:${closeMin} ${closePeriod})`;
  const customRangeInput = document.getElementById("custom-time-range-input");
  const customDisplayBlock = document.getElementById(
    "custom-interval-display-block"
  );

  if (customRangeInput && customDisplayBlock) {
    customRangeInput.value = formattedIntervalString;
    customDisplayBlock.textContent = formattedIntervalString;
    customDisplayBlock.style.display = "flex";
  }
  closeIntervalPickerModal();
}

// =============================================================
//  REPORT LISTING MODULE
// =============================================================
function openReportModal(listingName) {
  const modal = document.getElementById("modal-report-listing");
  const nameInput = document.getElementById("report-listing-name");
  if (modal && nameInput) {
    nameInput.value = listingName;
    modal.classList.add("active");
  }
}

function closeReportModal() {
  const modal = document.getElementById("modal-report-listing");
  if (modal) {
    modal.classList.remove("active");
    document.getElementById("report-reason").value = "";
    document.getElementById("report-details").value = "";
  }
}

function submitReport() {
  const reason = document.getElementById("report-reason").value;
  const listingName = document.getElementById("report-listing-name").value;

  if (!reason) {
    alert("Please select a reason for reporting.");
    return;
  }

  alert(
    `Thank you for your report on "${listingName}". Our moderation team will review this shortly.`
  );
  closeReportModal();
}

// =============================================================
//  LIVE SEARCH CONTROLLER MATRIX
// =============================================================
function handleLiveSearch(query) {
  const panel = document.getElementById("search-results-panel");
  const list = document.getElementById("search-results-list");
  const empty = document.getElementById("search-empty-state");
  const filterContent = document.getElementById("filter-screen-content");

  let zoneFilter = "All";
  document.querySelectorAll("#zone-filter-row .selection-tag").forEach((b) => {
    if (b.classList.contains("active")) zoneFilter = b.textContent.trim();
  });

  const activeTags = [];
  document
    .querySelectorAll("#placeholder-tags-container .selection-tag.active")
    .forEach((b) => {
      const dataVal = b.getAttribute("data-tag-value");
      if (dataVal) activeTags.push(dataVal.trim());
    });

  if (!query && activeTags.length === 0 && zoneFilter === "All") {
    panel.style.display = "none";
    filterContent.style.display = "block";
    return;
  }

  filterContent.style.display = "none";
  panel.style.display = "block";
  list.innerHTML = "";

  const lower = query?.toLowerCase() || "";

  const activeTab = document.querySelector(
    "#filter-category-tabs .filter-tab-item.active"
  );
  const catKey = activeTab
    ? activeTab.getAttribute("onclick").match(/'(\w+)'/)?.[1]
    : "all";

  const source = typeof REAL_LISTINGS !== 'undefined' ? REAL_LISTINGS : MOCK_LISTINGS;
  const results = source.filter((item) => {
    if (catKey && catKey !== "all" && item.category !== catKey) return false;
    if (
      zoneFilter !== "All" &&
      item.zone.toLowerCase() !== zoneFilter.toLowerCase()
    )
      return false;

    if (activeTags.length > 0) {
      const validFilters = activeTags.map((t) => t.trim().toLowerCase());
      const itemTagsLower = item.tags.map((t) => t.toLowerCase());
      if (!validFilters.every((t) => itemTagsLower.includes(t))) return false;
    }

    if (query) {
      return (
        item.title.toLowerCase().includes(lower) ||
        item.tags.some((t) => t.toLowerCase().includes(lower)) ||
        item.category.toLowerCase().includes(lower)
      );
    }

    return true;
  });

  if (results.length === 0) {
    empty.style.display = "flex";
    list.style.display = "none";
    return;
  }

  empty.style.display = "none";
  list.style.display = "flex";

  results.forEach((item) => {
    const card = document.createElement("div");
    card.className = "business-catalog-card";
    if (item.modalId) {
      card.onclick = () => openModal(item.modalId);
      card.style.cursor = "pointer";
    }

    const imgHTML = item.image
      ? `<div class="business-photo"><img src="${item.image}" alt="${item.title}" /></div>`
      : `<div class="business-photo business-photo-placeholder"><i class="fas fa-image"></i></div>`;

    const tagPills = item.tags
      .map((t) => `<span class="business-tag-item">${t}</span>`)
      .join("");

    card.innerHTML = `${imgHTML}
      <div class="business-details">
        <div class="business-header-row">
          <span class="business-title">${item.title}</span>
          <span class="zone-badge">${item.zone} Campus</span>
        </div>
        <div class="business-status-row">
          <span class="status-indicator open">${item.status}</span>
          <span class="business-hours">| ${item.hours}</span>
        </div>
        <div class="business-tags-container">${tagPills}</div>
      </div>`;

    list.appendChild(card);
  });
}

// =============================================================
//  FILTER SCREEN INTERFACES
// =============================================================
function toggleZoneFilter(btn, zone) {
  document
    .querySelectorAll("#zone-filter-row .selection-tag")
    .forEach((b) => b.classList.remove("active"));
  btn.classList.add("active");
  const q = document.getElementById("live-search-input")?.value.trim() || "";
  handleLiveSearch(q);
}

function toggleFilterTag(el) {
  el.classList.toggle("active");
  const q = document.getElementById("live-search-input")?.value.trim() || "";
  handleLiveSearch(q);
}

function switchFilterCategory(tab, category) {
  const scroller = tab.parentElement;
  scroller
    .querySelectorAll(".filter-tab-item")
    .forEach((t) => t.classList.remove("active"));
  tab.classList.add("active");

  scroller.scrollTo({
    left: tab.offsetLeft - scroller.clientWidth / 2 + tab.clientWidth / 2,
    behavior: "smooth",
  });

  const tagsContainer = document.getElementById("placeholder-tags-container");
  const tagsWrapper = document.getElementById("dynamic-tags-section");
  tagsContainer.innerHTML = "";

  const tags = CATEGORY_TAGS_MAP[category] || [];
  if (tags.length === 0) {
    tagsWrapper.style.display = "none";
  } else {
    tags.forEach((t) => {
      const btn = document.createElement("button");
      btn.className = "selection-tag";
      btn.textContent = t;
      btn.setAttribute("data-tag-value", t);
      btn.onclick = function () {
        toggleFilterTag(this);
      };
      tagsContainer.appendChild(btn);
    });
    tagsWrapper.style.display = "block";
  }

  const q = document.getElementById("live-search-input")?.value.trim() || "";
  handleLiveSearch(q);
}

// =============================================================
//  COMMENTS INTERFACES
// =============================================================
function submitComment(inputId, listId) {
  const input = document.getElementById(inputId);
  const list = document.getElementById(listId);
  if (!input || !list || !input.value.trim()) return;
  const div = document.createElement("div");
  div.className = "comment-bubble";
  div.innerHTML = `<span class="comment-author">You</span><span class="comment-text">${input.value.trim()}</span>`;
  list.appendChild(div);
  input.value = "";
  list.scrollTop = list.scrollHeight;
}

// =============================================================
//  MODALS OVERLAYS LAYOUTS
// =============================================================
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add("active");
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove("active");
}

function toggleFavoriteListing(btn) {
  const button = btn || document.querySelector('.hero-fav-btn');
  if (!button) return;
  const icon = button.querySelector('i');
  const listingName = button.dataset.listing;
  if (!icon || !listingName) return;

  const index = userSavedFavoritesRegistry.indexOf(listingName);
  const isFavorite = icon.classList.contains('fas');

  if (isFavorite) {
    icon.classList.replace('fas', 'far');
    icon.style.color = '';
    if (index !== -1) {
      userSavedFavoritesRegistry.splice(index, 1);
    }
  } else {
    icon.classList.replace('far', 'fas');
    icon.style.color = '#ff6b6b';
    if (index === -1) {
      userSavedFavoritesRegistry.push(listingName);
    }
  }

  // Save to localStorage
  localStorage.setItem('userFavorites', JSON.stringify(userSavedFavoritesRegistry));
  syncFavoritesUIComponent();
  // Sync all heart icons on page
  initializeFavoriteIcons();
}

// =============================================================
//  PROFILE ACCORDIONS
// =============================================================
function toggleAccordion(header) {
  header.parentElement.classList.toggle("open");
}

// =============================================================
//  CREDENTIALS UTILITIES
// =============================================================
function triggerForgotPassword() {
  const u = prompt("Enter your registered Username or Student Number:");
  if (!u) return;
  u.trim()
    ? alert(`Password reset link simulated for: "${u.trim()}"`)
    : alert("Please enter a valid username.");
}
