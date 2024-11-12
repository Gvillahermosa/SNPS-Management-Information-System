document.addEventListener('DOMContentLoaded', function() {
    if (messageType && messageText) {
        const icon = messageType === "success" ? "fa-solid fa-circle-check" : "fa-solid fa-circle-exclamation";
        const title = messageType.charAt(0).toUpperCase() + messageType.slice(1);

        createToast(messageType, icon, title, messageText);
    }
});

function createToast(type, icon, title, text) {
    let notifications = document.querySelector('.notifications');
    let newToast = document.createElement('div');
    newToast.innerHTML = `
        <div class="toast ${type}">
            <i class="${icon}"></i>
            <div class="content">
                <div class="title">${title}</div>
                <span>${text}</span>
            </div>
            <i class="close fa-solid fa-xmark" style="cursor: pointer;" onclick="(this.parentElement).remove()"></i>
        </div>`;
    notifications.appendChild(newToast);
    setTimeout(() => newToast.remove(), 5000);
}
