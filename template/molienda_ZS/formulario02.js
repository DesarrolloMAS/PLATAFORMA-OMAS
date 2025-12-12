function toggleField(checkbox, fieldId) {
    var field = document.getElementById(fieldId);
    if (checkbox.checked) {
        field.style.display = 'block';
    } else {
        field.style.display = 'none';
    }
}

