/**
 * patient-test-take.js
 *
 * Corrección del test en el cliente.
 * Para MC y V/F: compara la respuesta seleccionada con la correcta.
 * Para OPEN_ENDED: simplemente marca como respondida (sin corrección automática).
 */

function correctTest() {
    const cards = document.querySelectorAll('.tq-card');
    let totalScore = 0;
    let allAnswered = true;

    cards.forEach(card => {
        const type = card.dataset.type;
        const feedbackDiv = card.querySelector('.tq-feedback');

        if (type === 'MULTIPLE_CHOICE' || type === 'TRUE_FALSE') {
            const selected = card.querySelector('input[type="radio"]:checked');

            if (!selected) {
                allAnswered = false;
                feedbackDiv.style.display = 'block';
                feedbackDiv.className = 'tq-feedback feedback-unanswered';
                feedbackDiv.textContent = 'No has respondido esta pregunta.';
                return;
            }

            const correctAnswer = card.querySelector('.correct-answer').value;
            const feedbackCorrect = card.querySelector('.feedback-correct').value;
            const feedbackIncorrect = card.querySelector('.feedback-incorrect').value;
            const points = parseFloat(card.querySelector('.tq-points').textContent);

            // Marcar opciones
            const options = card.querySelectorAll('.tq-option');
            options.forEach(opt => {
                const val = opt.dataset.value;
                opt.classList.remove('option-selected-correct', 'option-selected-incorrect', 'option-is-correct');

                if (val === correctAnswer) {
                    opt.classList.add('option-is-correct');
                }
                if (val === selected.value && val === correctAnswer) {
                    opt.classList.add('option-selected-correct');
                } else if (val === selected.value && val !== correctAnswer) {
                    opt.classList.add('option-selected-incorrect');
                }
            });

            // Desactivar inputs
            card.querySelectorAll('input[type="radio"]').forEach(r => r.disabled = true);

            // Mostrar feedback
            const isCorrect = selected.value === correctAnswer;
            feedbackDiv.style.display = 'block';

            if (isCorrect) {
                totalScore += points;
                feedbackDiv.className = 'tq-feedback feedback-correct';
                feedbackDiv.textContent = feedbackCorrect || '✅ ¡Correcto!';
            } else {
                feedbackDiv.className = 'tq-feedback feedback-incorrect';
                feedbackDiv.textContent = feedbackIncorrect || '❌ Incorrecto.';
            }

        } else if (type === 'OPEN_ENDED') {
            const textarea = card.querySelector('.tq-open-answer');
            if (textarea && textarea.value.trim()) {
                feedbackDiv.style.display = 'block';
                feedbackDiv.className = 'tq-feedback feedback-neutral';
                feedbackDiv.textContent = 'Respuesta registrada. Esta pregunta se evalúa manualmente.';
                textarea.disabled = true;
            } else {
                feedbackDiv.style.display = 'block';
                feedbackDiv.className = 'tq-feedback feedback-unanswered';
                feedbackDiv.textContent = 'No has respondido esta pregunta.';
            }
        }
    });

    // Mostrar resultado
    const resultDiv = document.getElementById('testResult');
    const scoreSpan = document.getElementById('resultScore');
    scoreSpan.textContent = totalScore % 1 === 0 ? totalScore : totalScore.toFixed(1);
    resultDiv.style.display = 'flex';

    // Ocultar botón de corregir
    document.getElementById('btnCorrect').style.display = 'none';

    // Scroll al resultado
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}