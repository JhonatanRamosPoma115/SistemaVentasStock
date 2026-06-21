function filterCat(cat) {
    const url = new URL(window.location);
    if (cat) url.searchParams.set('category', cat);
    else url.searchParams.delete('category');
    url.searchParams.delete('page');
    window.location = url.toString();
}
