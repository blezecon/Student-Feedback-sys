// 1. Theme Switcher (Light / Dark / Auto)
function setTheme(theme) {
    const isDark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    localStorage.setItem('sfs-theme', theme);

    // Update navbar dropdown icon and text
    const btn = document.querySelector(`[data-bs-theme-value="${theme}"]`);
    if (btn) {
        document.querySelectorAll('[data-bs-theme-value]').forEach(el => el.classList.toggle('active', el === btn));
        const svg = btn.querySelector('svg');
        const activeIcon = document.querySelector('.theme-icon-active');
        if (svg && activeIcon) activeIcon.innerHTML = svg.outerHTML;

        const label = document.getElementById('theme-label');
        if (label) label.textContent = theme === 'auto' ? 'Auto' : btn.innerText.trim();
    }
}

document.querySelectorAll('[data-bs-theme-value]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        setTheme(btn.dataset.bsThemeValue);
    });
});

// Set initial theme state on page load
setTheme(localStorage.getItem('sfs-theme') || 'auto');

// Auto-switch theme if user changes browser/OS settings
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const saved = localStorage.getItem('sfs-theme');
    if (!saved || saved === 'auto') setTheme('auto');
});

// 2. Student vs Teacher Toggle on Registration
const studentBox = document.getElementById('student-fields');
const teacherBox = document.getElementById('teacher-fields');

function toggleRole(isTeacher) {
    if (!studentBox || !teacherBox) return;
    studentBox.classList.toggle('d-none', isTeacher);
    teacherBox.classList.toggle('d-none', !isTeacher);
    studentBox.querySelectorAll('input, select').forEach(el => el.disabled = isTeacher);
    teacherBox.querySelectorAll('input, select').forEach(el => el.disabled = !isTeacher);
}

document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', () => toggleRole(radio.value === 'teacher'));
});

// Set initial role state on page load
const checkedRole = document.querySelector('input[name="role"]:checked');
if (checkedRole) toggleRole(checkedRole.value === 'teacher');
