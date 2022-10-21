import Alpine from 'alpinejs';
import FormsAlpinePlugin from '../../vendor/filament/forms/dist/module.esm';
import NotificationsAlpinePlugin from '../../vendor/filament/notifications/dist/module.esm';

Alpine.plugin(FormsAlpinePlugin);
Alpine.plugin(NotificationsAlpinePlugin);

Alpine.store(
    'theme',
    window.matchMedia('(prefers-color-scheme: dark)').matches
        ? 'dark'
        : 'light',
)

window.addEventListener('dark-mode-toggled', (event) => {
    Alpine.store('theme', event.detail)
})

window
    .matchMedia('(prefers-color-scheme: dark)')
    .addEventListener('change', (event) => {
        Alpine.store('theme', event.matches ? 'dark' : 'light')
    })

window.Alpine = Alpine;

Alpine.start();
