(() => {
    const revealTargets = document.querySelectorAll(
        "section, .section-title, .cat-card, .grid-item, .review-card, .about-section, .extreme-readouts"
    );

    if (!revealTargets.length || !("IntersectionObserver" in window)) {
        return;
    }

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reducedMotion) {
        return;
    }

    revealTargets.forEach((element, index) => {
        if (element.classList.contains("hero-logo-section")) {
            return;
        }

        element.setAttribute("data-extreme-reveal", index % 2 === 0 ? "left" : "right");
        if (element.matches(".cat-card, .grid-item, .review-card")) {
            element.style.setProperty("--extreme-delay", `${(index % 5) * 55}ms`);
        }
    });

    document.body.classList.add("extreme-ready");

    const observer = new IntersectionObserver((entries, currentObserver) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                currentObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: "0px 0px -35px 0px"
    });

    revealTargets.forEach((element) => {
        if (element.hasAttribute("data-extreme-reveal")) {
            observer.observe(element);
        } else {
            element.classList.add("is-visible");
        }
    });
})();
