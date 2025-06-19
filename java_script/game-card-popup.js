// Modal functionality for game cards
document.addEventListener("DOMContentLoaded", function () {
    const banglaNumbers = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    const modal = document.getElementById("gameModal");
    const modalText = document.getElementById("gameModalText");
    const closeBtn = document.querySelector(".game-modal-close");

    // Show modal
    function showModal(msg) {
        modalText.innerHTML = msg;
        modal.style.display = "block";
    }
    // Hide modal
    function hideModal() {
        modal.style.display = "none";
    }

    // Event for close button
    closeBtn.onclick = hideModal;

    // Close modal on clicking outside content
    window.onclick = function(event) {
        if (event.target === modal) hideModal();
    };

    // Attach to all .game-card except .coming-soon
    document.querySelectorAll('.game-card:not(.coming-soon)').forEach(card => {
        card.addEventListener("click", function () {
            // Generate random date & time (next 10 days)
            const now = new Date();
            const randomOffset = Math.floor(Math.random() * 10 * 24 * 60 * 60 * 1000);
            const randomDate = new Date(now.getTime() + randomOffset);

            // Format to Bangla
            const y = randomDate.getFullYear().toString().replace(/[0-9]/g, d => banglaNumbers[d]);
            const m = (randomDate.getMonth() + 1).toString().padStart(2, '0').replace(/[0-9]/g, d => banglaNumbers[d]);
            const d = randomDate.getDate().toString().padStart(2, '0').replace(/[0-9]/g, d => banglaNumbers[d]);
            const h = randomDate.getHours().toString().padStart(2, '0').replace(/[0-9]/g, d => banglaNumbers[d]);
            const min = randomDate.getMinutes().toString().padStart(2, '0').replace(/[0-9]/g, d => banglaNumbers[d]);
            const banglaDateTime = `${y}-${m}-${d} ${h}:${min}`;

            showModal(`এই গেমটি শুরু হবে: <br><strong style="color:#28a745">${banglaDateTime}</strong>`);
        });
    });
});