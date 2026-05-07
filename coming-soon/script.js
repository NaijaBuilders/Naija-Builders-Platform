const words = ['materials', 'suppliers', 'orders', 'projects'];
const wordElement = document.getElementById('rotating-word');
let wordIndex = 0;

function rotateWord() {
  if (!wordElement) {
    return;
  }

  wordIndex = (wordIndex + 1) % words.length;
  wordElement.animate(
    [
      { opacity: 1, transform: 'translateY(0)' },
      { opacity: 0, transform: 'translateY(-8px)' },
    ],
    {
      duration: 180,
      easing: 'ease-out',
      fill: 'forwards',
    }
  ).onfinish = () => {
    wordElement.textContent = words[wordIndex];
    wordElement.animate(
      [
        { opacity: 0, transform: 'translateY(8px)' },
        { opacity: 1, transform: 'translateY(0)' },
      ],
      {
        duration: 220,
        easing: 'ease-out',
        fill: 'forwards',
      }
    );
  };
}

window.setInterval(rotateWord, 2200);

document.querySelectorAll('.js-app-download').forEach((button) => {
  button.addEventListener('click', () => {
    const platform = button.dataset.platform || 'mobile';
    const group = button.closest('.app-download-group');
    const status = group ? group.querySelector('.app-status') : null;
    const message = 'Coming soon';

    if (status) {
      status.textContent = message;
      return;
    }

    window.alert(message);
  });
});
