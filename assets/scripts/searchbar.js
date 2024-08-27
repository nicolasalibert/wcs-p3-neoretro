const toggleDisplay = (buttonSelector, targetSelector, defaultDisplay) => {
    const button = document.querySelector(buttonSelector);
    const target = document.querySelector(targetSelector);

    button.addEventListener('click', () => {
        target.style.display = target.classList.contains("hidden") ? defaultDisplay : "none";
        target.classList.toggle("hidden");
    });
}

const toggleSorting = (sortSelector) => {
    const button = document.querySelector(`.sort.${sortSelector}`);
    const target = document.querySelector(`.sort.${sortSelector} img`);
    const sortBy = document.querySelector('input#game_search_sort_by');
    const sortOrder = document.querySelector('input#game_search_sort_order');

    if (sortBy.value === sortSelector && sortOrder.value === 'ASC') {
        target.classList.toggle('reversed');
    }

    button.addEventListener('click', function() {
        target.classList.toggle('reversed');
        sortBy.setAttribute('value', sortSelector);
        sortOrder.setAttribute('value', target.classList.contains('reversed') ? 'ASC' : 'DESC');
    });
}

toggleDisplay('.btn.settings', '#search-modal', 'flex');
toggleDisplay('.filter p', '.filter .params#category', 'block');

toggleSorting('title');
toggleSorting('score');
toggleSorting('time');

document.querySelector('.btn.search').addEventListener('click', () => {
    document.querySelector('form#searchbar').submit();
})
