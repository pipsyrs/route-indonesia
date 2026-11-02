/** One modal for every Apply button; preselect the clicked job. */
export function initJobApply(dialog) {
    const select = dialog.querySelector('[data-job-select]');

    dialog.addEventListener('modal:open', (event) => {
        const title = event.detail.trigger?.dataset.jobTitle;
        if (title && [...select.options].some((option) => option.value === title)) {
            select.value = title;
        }
    });
}
