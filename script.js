function showOptions() {
    var type = document.getElementById('type').value;
    document.getElementById('courseOptions').style.display = (type === 'course') ? 'block' : 'none';
    document.getElementById('schoolOptions').style.display = (type === 'school') ? 'block' : 'none';
}

function calculateBalance() {
    let x = parseFloat(document.getElementById('x').value) || 0;
    let y = parseFloat(document.getElementById('y').value) || 0;
    document.getElementById('remaining').innerText = (10000 - (x + y)); // assume total course = 10000
    document.getElementById('handBalance').innerText = (x - y);
}
