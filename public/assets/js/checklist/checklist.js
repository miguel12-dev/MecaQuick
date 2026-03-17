(() => {
    const form = document.getElementById("checklistForm");
    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const steps = Array.from(form.querySelectorAll(".checklist-step"));
    const progressBar = document.getElementById("checklistProgressBar");
    const progressLabel = document.getElementById("checklistProgressLabel");
    const messageBox = document.getElementById("checklistMessage");
    const tokenInput = document.getElementById("checklistToken");
    const totalQuestions = Number(form.dataset.total || 0);
    const saveUrl = String(form.dataset.saveUrl || "");

    if (!(progressBar instanceof HTMLElement) || !(progressLabel instanceof HTMLElement) || !(tokenInput instanceof HTMLInputElement) || saveUrl === "") {
        return;
    }

    let currentStep = 0;
    let isSaving = false;

    const showMessage = (text, isError = false) => {
        if (!(messageBox instanceof HTMLElement)) {
            return;
        }
        messageBox.textContent = text;
        messageBox.classList.remove("checklist-message--hidden", "alert--error", "alert--success");
        messageBox.classList.add(isError ? "alert--error" : "alert--success");
    };

    const hideMessage = () => {
        if (!(messageBox instanceof HTMLElement)) {
            return;
        }
        messageBox.textContent = "";
        messageBox.classList.add("checklist-message--hidden");
    };

    const getAnsweredCount = () => {
        const answeredGroups = new Set();
        const checked = form.querySelectorAll('input[type="radio"][name^="responses["]:checked');
        checked.forEach((input) => {
            if (input instanceof HTMLInputElement) {
                answeredGroups.add(input.name);
            }
        });
        return answeredGroups.size;
    };

    const updateProgress = () => {
        const answered = getAnsweredCount();
        const percentage = totalQuestions > 0 ? Math.round((answered / totalQuestions) * 100) : 0;
        progressBar.style.width = `${percentage}%`;
        progressLabel.textContent = `${answered}/${totalQuestions} (${percentage}%)`;
    };

    const showStep = (stepIndex) => {
        steps.forEach((step, index) => {
            step.classList.toggle("is-active", index === stepIndex);
        });
        currentStep = stepIndex;
        hideMessage();
    };

    const isFinalStep = () => {
        const step = steps[currentStep];
        return step instanceof HTMLElement && step.dataset.step === "final";
    };

    const validateCurrentStep = () => {
        const step = steps[currentStep];
        if (!(step instanceof HTMLElement)) {
            return false;
        }

        const requiredInputs = Array.from(step.querySelectorAll("input[required], textarea[required]"));
        for (const input of requiredInputs) {
            if (!(input instanceof HTMLInputElement) && !(input instanceof HTMLTextAreaElement)) {
                continue;
            }
            if (input.value.trim() === "") {
                input.reportValidity();
                input.focus();
                showMessage("Complete todos los campos obligatorios antes de continuar.", true);
                return false;
            }
            if (input.type === "number" && Number(input.value) < 0) {
                showMessage("El valor no puede ser negativo.", true);
                input.focus();
                return false;
            }
        }

        if (currentStep > 0 && !isFinalStep()) {
            const puntoId = step.dataset.puntoId;
            if (puntoId) {
                const groupName = `responses[${puntoId}]`;
                const selected = step.querySelector(`input[name="${CSS.escape(groupName)}"]:checked`);
                if (!(selected instanceof HTMLInputElement)) {
                    showMessage("Debe responder la pregunta actual antes de continuar.", true);
                    return false;
                }
            }
        }

        return true;
    };

    const buildPayload = (isFinal) => {
        const formData = new FormData(form);
        formData.set("token", tokenInput.value.trim());
        formData.set("ultima_pregunta", String(Math.max(0, Math.min(currentStep, totalQuestions))));
        formData.set("finalizado", isFinal ? "1" : "0");
        return formData;
    };

    const saveStep = async (isFinal) => {
        if (isSaving) {
            return false;
        }

        isSaving = true;
        try {
            const response = await fetch(saveUrl, {
                method: "POST",
                body: buildPayload(isFinal),
            });

            const result = await response.json();
            if (!response.ok || !result.ok) {
                const message = typeof result.message === "string"
                    ? result.message
                    : "No fue posible guardar la información.";
                showMessage(message, true);
                return false;
            }

            if (typeof result.token === "string" && result.token !== "") {
                tokenInput.value = result.token;
            }

            if (result.progress && typeof result.progress.answered === "number" && typeof result.progress.total === "number" && typeof result.progress.percentage === "number") {
                progressBar.style.width = `${result.progress.percentage}%`;
                progressLabel.textContent = `${result.progress.answered}/${result.progress.total} (${result.progress.percentage}%)`;
            } else {
                updateProgress();
            }

            showMessage(isFinal ? "Checklist finalizado y guardado correctamente." : "Avance guardado correctamente.");
            if (isFinal && typeof result.inspeccion_id === "number" && form.dataset.redirectAprendiz === "1") {
                mostrarOpcionOrdenRepuestos(result.inspeccion_id);
                return true;
            }
            return true;
        } catch (error) {
            showMessage("Error de red al guardar el avance.", true);
            return false;
        } finally {
            isSaving = false;
        }
    };

    form.addEventListener("click", async (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) {
            return;
        }

        const action = target.dataset.action;
        if (!action) {
            return;
        }

        if (action === "back") {
            const previousStep = Math.max(0, currentStep - 1);
            showStep(previousStep);
            return;
        }

        if (action === "next") {
            if (!validateCurrentStep()) {
                return;
            }

            const saved = await saveStep(false);
            if (!saved) {
                return;
            }

            const nextStep = Math.min(currentStep + 1, steps.length - 1);
            showStep(nextStep);
            updateProgress();
            return;
        }

        if (action === "finish") {
            if (!validateCurrentStep()) {
                return;
            }

            if (!isFinalStep()) {
                const answered = getAnsweredCount();
                if (answered !== totalQuestions) {
                    showMessage("Debe completar todas las preguntas antes de finalizar.", true);
                    return;
                }
            }

            await saveStep(true);
        }
    });

    form.addEventListener("change", () => {
        updateProgress();
    });

    const mostrarOpcionOrdenRepuestos = (inspeccionId) => {
        const div = document.createElement("div");
        div.className = "checklist-opcion-orden";
        div.setAttribute("role", "dialog");
        div.setAttribute("aria-label", "Opciones al finalizar");
        div.innerHTML = `
            <div class="checklist-opcion-orden__overlay"></div>
            <div class="checklist-opcion-orden__modal">
                <h3 class="checklist-opcion-orden__titulo">Checklist finalizado</h3>
                <p class="checklist-opcion-orden__texto">¿Desea continuar con orden de repuestos o finalizar el mantenimiento?</p>
                <div class="checklist-opcion-orden__acciones">
                    <a href="/orden-repuestos/crear/${inspeccionId}" class="btn btn--primary">Orden de repuestos</a>
                    <a href="/recepcion/revision/${inspeccionId}" class="btn btn--secondary">Finalizar mantenimiento</a>
                </div>
            </div>
        `;
        div.querySelector(".checklist-opcion-orden__overlay").addEventListener("click", () => div.remove());
        document.body.appendChild(div);
    };

    const skipCabecera = form.dataset.skipCabecera === "1";
    showStep(skipCabecera ? 1 : 0);
    updateProgress();
})();
