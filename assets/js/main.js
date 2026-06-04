(function () {
  "use strict";

  document.documentElement.classList.add("js");

  const body = document.body;
  const mobileNavToggle = document.querySelector(".mobile-nav-toggle");
  const navLinks = document.querySelectorAll("#navmenu a");
  const scrollTop = document.querySelector(".scroll-top");

  function closeMobileNav() {
    body.classList.remove("mobile-nav-active");
    if (mobileNavToggle) {
      mobileNavToggle.setAttribute("aria-expanded", "false");
      const icon = mobileNavToggle.querySelector("i");
      if (icon) {
        icon.classList.add("bi-list");
        icon.classList.remove("bi-x");
      }
    }
  }

  if (mobileNavToggle) {
    mobileNavToggle.addEventListener("click", () => {
      const isOpen = body.classList.toggle("mobile-nav-active");
      mobileNavToggle.setAttribute("aria-expanded", String(isOpen));
      const icon = mobileNavToggle.querySelector("i");
      if (icon) {
        icon.classList.toggle("bi-list", !isOpen);
        icon.classList.toggle("bi-x", isOpen);
      }
    });
  }

  navLinks.forEach((link) => {
    link.addEventListener("click", closeMobileNav);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && body.classList.contains("mobile-nav-active")) {
      closeMobileNav();
    }
  });

  function toggleScrollTop() {
    if (!scrollTop) return;
    window.scrollY > 320 ? scrollTop.classList.add("active") : scrollTop.classList.remove("active");
  }

  if (scrollTop) {
    scrollTop.addEventListener("click", (event) => {
      event.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
    window.addEventListener("load", toggleScrollTop);
    document.addEventListener("scroll", toggleScrollTop, { passive: true });
  }

  const year = document.querySelector("[data-current-year]");
  if (year) {
    year.textContent = new Date().getFullYear();
  }

  const revealItems = document.querySelectorAll("[data-reveal]");
  if ("IntersectionObserver" in window && revealItems.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );
    revealItems.forEach((item) => observer.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add("is-visible"));
  }

  function setStatus(form, type, message) {
    const status = form.querySelector("[data-form-status]");
    if (!status) return;
    status.textContent = message;
    status.classList.remove("is-error", "is-success");
    status.classList.add("is-visible", type === "success" ? "is-success" : "is-error");
  }

  function clearStatus(form) {
    const status = form.querySelector("[data-form-status]");
    if (!status) return;
    status.textContent = "";
    status.classList.remove("is-visible", "is-error", "is-success");
  }

  function validateForm(form) {
    let isValid = true;
    const fields = form.querySelectorAll("[required]");

    fields.forEach((field) => {
      field.classList.remove("is-invalid");
      const value = field.value.trim();
      const minLength = Number(field.getAttribute("minlength") || 1);

      if (!value || value.length < minLength) {
        field.classList.add("is-invalid");
        isValid = false;
      }

      if (field.type === "email" && value && !field.checkValidity()) {
        field.classList.add("is-invalid");
        isValid = false;
      }
    });

    return isValid;
  }

  document.querySelectorAll("[data-contact-form]").forEach((form) => {
    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      clearStatus(form);

      if (!validateForm(form)) {
        setStatus(form, "error", "Merci de remplir correctement tous les champs requis.");
        return;
      }

      const submitButton = form.querySelector("button[type='submit']");
      const originalText = submitButton ? submitButton.innerHTML : "";
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-arrow-repeat"></i> Envoi en cours';
      }

      try {
        const response = await fetch(form.action, {
          method: "POST",
          body: new FormData(form),
          headers: { "X-Requested-With": "XMLHttpRequest" }
        });

        const text = await response.text();
        let payload = {};
        try {
          payload = JSON.parse(text);
        } catch (error) {
          payload = { ok: response.ok, message: text };
        }

        if (!response.ok || !payload.ok) {
          throw new Error(payload.message || "Le message n'a pas pu être envoyé.");
        }

        setStatus(form, "success", payload.message || "Votre message a été envoyé. Vous recevrez une réponse par email.");
        form.reset();
      } catch (error) {
        setStatus(form, "error", error.message || "Une erreur est survenue pendant l'envoi.");
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        }
      }
    });
  });
})();
