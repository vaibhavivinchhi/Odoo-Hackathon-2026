// TransitOps front-end helpers
document.addEventListener('DOMContentLoaded', function () {
  var dateEl = document.getElementById('topbar-date');
  if (dateEl && !dateEl.textContent) {
    dateEl.textContent = new Date().toLocaleDateString('en-IN', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
  }
});

