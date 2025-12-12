function toggleField(checkbox, fieldId) {
    const field = document.getElementById(fieldId);
    if (checkbox.checked) {
        field.style.display = 'block';
    } else {
        field.style.display = 'none';
    }
}