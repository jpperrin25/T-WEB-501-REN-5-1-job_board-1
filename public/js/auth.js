
document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       TYPE DE COMPTE : CANDIDAT / ENTREPRISE
       ===================================================== */

    const accountButtons = document.querySelectorAll(
        ".role-button, .account-type-option"
    );

    function updateCompanyFields(isCompany) {
        const companyFields = document.querySelector("#companyFields");

        if (!companyFields) {
            return;
        }

        // Le style inline évite qu'une règle CSS de grille
        // rende visibles les champs malgré l'attribut hidden.
        companyFields.hidden = !isCompany;
        companyFields.style.display = isCompany ? "grid" : "none";

        companyFields.querySelectorAll("input, textarea").forEach(function (field) {
            field.disabled = !isCompany;

            if (field.name === "description") {
                field.required = false;
            } else {
                field.required = isCompany;
            }
        });
    }

    accountButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const container = button.parentElement;

            container.querySelectorAll(
                ".role-button, .account-type-option"
            ).forEach(function (item) {
                item.classList.remove("active");
                item.setAttribute("aria-pressed", "false");
            });

            button.classList.add("active");
            button.setAttribute("aria-pressed", "true");

            // Le choix de compte à l'inscription contrôle les champs entreprise.
            if (button.classList.contains("account-type-option")) {
                updateCompanyFields(button.dataset.role === "company");
            }

        });

    });

    // État initial : Candidat sélectionné, champs entreprise masqués.
    const initialCompanyButton = document.querySelector(
        '.account-type-option[data-role="company"]'
    );

    updateCompanyFields(
        initialCompanyButton !== null &&
        initialCompanyButton.classList.contains("active")
    );


    /* =====================================================
       VISIBILITÉ DU MOT DE PASSE
       ===================================================== */

    document.querySelectorAll(".password-wrapper").forEach(function (wrapper) {

        const passwordInput = wrapper.querySelector(
            "input[type='password'], input[type='text']"
        );

        const passwordToggle = wrapper.querySelector(".password-toggle");

        if (!passwordInput || !passwordToggle) {
            return;
        }

        passwordToggle.addEventListener("click", function () {

            const isHidden = passwordInput.type === "password";

            passwordInput.type = isHidden ? "text" : "password";

            passwordToggle.setAttribute(
                "aria-label",
                isHidden
                    ? "Masquer le mot de passe"
                    : "Afficher le mot de passe"
            );

        });

    });


    /* =====================================================
       ENVOI DES FORMULAIRES
       ===================================================== */

    document.querySelectorAll(".auth-card form").forEach(function (form) {

        form.addEventListener("submit", function () {

            const submitButton = form.querySelector('button[type="submit"]');

            if (!submitButton) {
                return;
            }

            submitButton.disabled = true;

            if (form.dataset.authForm === "register") {
                submitButton.textContent = "Création du compte...";
            } else {
                submitButton.textContent = "Connexion...";
            }

        });

    });



    /* =====================================================
       MENU BURGER INVITÉ
       ===================================================== */

    document.querySelectorAll("[data-burger-menu]").forEach(function (menu) {
        const toggle = menu.querySelector("[data-burger-toggle]");
        const panel = menu.querySelector("[data-burger-panel]");

        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener("click", function () {
            const isExpanded = toggle.getAttribute("aria-expanded") === "true";

            toggle.setAttribute("aria-expanded", String(!isExpanded));
            toggle.setAttribute(
                "aria-label",
                isExpanded ? "Ouvrir le menu" : "Fermer le menu"
            );

            panel.hidden = isExpanded;
        });

        panel.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                panel.hidden = true;
                toggle.setAttribute("aria-expanded", "false");
                toggle.setAttribute("aria-label", "Ouvrir le menu");
            });
        });

    });

});
// Modales JobBoard
document.querySelectorAll("[data-modal-open]").forEach(function (button) {
    button.addEventListener("click", function () {
        const modalId = button.getAttribute("data-modal-open");
        const modal = document.getElementById(modalId);

        if (modal && typeof modal.showModal === "function") {
            modal.showModal();
        }
    });
});

document.querySelectorAll("[data-modal-close]").forEach(function (button) {
    button.addEventListener("click", function () {
        const modal = button.closest("dialog");

        if (modal) {
            modal.close();
        }
    });
});

document.querySelectorAll(".jobboard-modal").forEach(function (modal) {
    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.close();
        }
    });
});

// Modales publiques : détails des entreprises et des offres
document.querySelectorAll("[data-company-modal]").forEach(function (button) {
    button.addEventListener("click", function () {
        const modal = document.getElementById("public-company-modal");
        if (!modal) return;

        const title = document.getElementById("public-company-modal-title");
        const description = document.getElementById("public-company-modal-description");
        const siret = document.getElementById("public-company-modal-siret");
        const jobsCount = document.getElementById("public-company-modal-jobs");
        const jobsList = document.getElementById("public-company-modal-jobs-list");
        const detailLink = document.getElementById("public-company-modal-detail-link");
        const jobsLink = document.getElementById("public-company-modal-jobs-link");

        if (title) title.textContent = button.dataset.name || "Entreprise";
        if (description) description.textContent = button.dataset.description || "Aucune description disponible.";
        if (siret) siret.textContent = button.dataset.siret || "Non renseigné";
        if (jobsCount) jobsCount.textContent = (button.dataset.jobs || "0") + " offre(s)";

        if (jobsList) {
            jobsList.replaceChildren();
            const source = button.closest(".company-card")?.querySelector("[data-company-jobs-source]");

            if (source) {
                source.querySelectorAll("p").forEach(function (item) {
                    const line = document.createElement("p");
                    line.textContent = item.textContent.trim();
                    jobsList.appendChild(line);
                });
            }
        }

        if (detailLink) detailLink.href = button.dataset.detailUrl || "#";
        if (jobsLink) jobsLink.href = button.dataset.jobsUrl || "#";

        if (typeof modal.showModal === "function") modal.showModal();
    });
});

document.querySelectorAll("[data-job-modal]").forEach(function (button) {
    button.addEventListener("click", function () {
        const modal = document.getElementById("public-job-modal");
        if (!modal) return;

        const setText = function (id, value, fallback) {
            const element = document.getElementById(id);
            if (element) element.textContent = value || fallback;
        };

        setText("public-job-modal-title", button.dataset.title, "Détail de l'offre");
        setText("public-job-modal-company", button.dataset.company, "Entreprise");
        setText("public-job-modal-city", button.dataset.city, "Non précisé");
        setText("public-job-modal-type", button.dataset.type, "Non précisé");
        setText("public-job-modal-schedule", button.dataset.schedule, "Non précisés");
        setText("public-job-modal-salary", button.dataset.salary, "Non renseignée");
        setText("public-job-modal-description", button.dataset.description, "Aucune description disponible.");

        const detailLink = document.getElementById("public-job-modal-detail-link");
        if (detailLink) {
            detailLink.href = button.dataset.jobUrl || "#";
        }

        const companyLink = document.getElementById("public-job-modal-company-link");
        if (companyLink) {
            const companyUrl = button.dataset.companyUrl || "";
            companyLink.href = companyUrl || "#";
            companyLink.hidden = !companyUrl;
        }

        if (typeof modal.showModal === "function") modal.showModal();
    });
});

// Confirmation de candidature : utilise le formulaire et la route existants.
let pendingApplicationForm = null;

document.querySelectorAll("[data-apply-confirm-form]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const modal = document.getElementById("job-apply-confirm-modal");
        const title = document.getElementById("job-apply-confirm-job-title");

        if (!modal || typeof modal.showModal !== "function") {
            // Repli : ne pas bloquer la candidature si dialog n'est pas pris en charge.
            form.submit();
            return;
        }

        pendingApplicationForm = form;
        if (title) {
            title.textContent = form.dataset.applyTitle || "Offre sélectionnée";
        }

        if (!modal.open) {
            modal.showModal();
        }
    });
});

const confirmApplicationButton = document.getElementById("job-apply-confirm-submit");
if (confirmApplicationButton) {
    confirmApplicationButton.addEventListener("click", function () {
        const form = pendingApplicationForm;
        if (!form) return;

        pendingApplicationForm = null;

        const modal = document.getElementById("job-apply-confirm-modal");
        if (modal && modal.open) {
            modal.close();
        }

        // Le navigateur envoie les champs cachés vers app_job_apply en POST.
        // Le contrôleur continue de vérifier le jeton CSRF et l'utilisateur connecté.
        form.submit();
    });
}

const applicationConfirmModal = document.getElementById("job-apply-confirm-modal");
if (applicationConfirmModal) {
    applicationConfirmModal.addEventListener("close", function () {
        pendingApplicationForm = null;
    });
}

document.querySelectorAll("[data-public-modal-close]").forEach(function (button) {
    button.addEventListener("click", function () {
        const modal = button.closest("dialog");
        if (modal) modal.close();
    });
});

