export function initSearchForm(form) {
    const keyword = form.querySelector('[data-search-q]');

    form.addEventListener('submit', () => {
        if (keyword) {
            keyword.value = keyword.value.trim();
        }
    });
}
