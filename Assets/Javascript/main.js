// Toggle navigation items for different sections
$('.student-btn').click(function() {
    $('.student-show').toggleClass("show2");
    $('.sidenav ul .second').toggleClass("rotate");
});

$('.jobplace-btn').click(function() {
    $('.jobplace-show').toggleClass("show3");
    $('.sidenav ul .third').toggleClass("rotate");
});

$('.classes-btn').click(function() {
    $('.classes-show').toggleClass("show4");
    $('.sidenav ul .fourth').toggleClass("rotate");
});

$('.teacher_add-btn').click(function() {
    $('.teacher_add-show').toggleClass("show5");
    $('.sidenav ul .fifth').toggleClass("rotate");
});

// Combined counter animation for student and class counts
document.addEventListener("DOMContentLoaded", function() {
    const animateCounter = (elementId, targetCount) => {
        let currentCount = 0;
        const element = document.getElementById(elementId);
        const increment = Math.ceil(targetCount / 100); // Controls animation speed

        const updateCount = () => {
            if (currentCount < targetCount) {
                currentCount += increment;
                if (currentCount > targetCount) currentCount = targetCount;
                element.textContent = `${currentCount}`;
                setTimeout(updateCount, 100); // Adjust delay here
            }
        };
        updateCount();
    };

    animateCounter('studentlist', parseInt(document.getElementById('studentlist').getAttribute('data-count')));
    animateCounter('subjectslist', parseInt(document.getElementById('subjectslist').getAttribute('data-count')));
    animateCounter('classlist', parseInt(document.getElementById('classlist').getAttribute('data-count')));
    animateCounter('teacherlist', parseInt(document.getElementById('teacherlist').getAttribute('data-count')));

});

document.getElementById('profile-picture').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('profile-img').src = e.target.result;
    };
    reader.readAsDataURL(file);
});
