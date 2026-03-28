// script.js - общие функции для складской системы

// Автоматическое скрытие алертов через 3 секунды
document.addEventListener('DOMContentLoaded', function() {
    // Скрываем алерты
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        });
    }, 3000);
    
    // Подтверждение удаления
    let deleteButtons = document.querySelectorAll('.delete-confirm, a[onclick*="confirm"]');
    deleteButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Вы уверены?')) {
                e.preventDefault();
            }
        });
    });
});

// Функция для поиска по таблице
function filterTable(inputId, tableId) {
    let input = document.getElementById(inputId);
    if (!input) return;
    
    input.addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#' + tableId + ' tbody tr');
        
        rows.forEach(function(row) {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
}

// Функция для форматирования чисел
function formatNumber(num) {
    return new Intl.NumberFormat('ru-RU').format(num);
}

// Функция для форматирования даты
function formatDate(dateString) {
    let date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
}

// Функция для расчета суммы
function calculateSum(price, quantity) {
    return price * quantity;
}

// Автоподстановка цены при выборе товара
function autoFillPrice(productSelect, priceInput) {
    if (!productSelect || !priceInput) return;
    
    productSelect.addEventListener('change', function() {
        let selected = this.options[this.selectedIndex];
        let price = selected.getAttribute('data-price');
        if (price && !priceInput.value) {
            priceInput.value = price;
        }
    });
}

// Экспорт таблицы в CSV
function exportToCSV(tableId, filename) {
    let table = document.getElementById(tableId);
    if (!table) return;
    
    let rows = table.querySelectorAll('tr');
    let csv = [];
    
    rows.forEach(function(row) {
        let cols = row.querySelectorAll('td, th');
        let rowData = [];
        cols.forEach(function(col) {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    let blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    let link = document.createElement('a');
    let url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename + '.csv');
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Печать страницы
function printPage() {
    window.print();
}

// Обновление страницы без перезагрузки (AJAX)
async function refreshData(url, containerId) {
    try {
        let response = await fetch(url);
        let html = await response.text();
        document.getElementById(containerId).innerHTML = html;
    } catch (error) {
        console.error('Ошибка:', error);
    }
}