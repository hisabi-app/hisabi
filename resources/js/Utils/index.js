export const getAppCurrency = () => {
    return window ? window.AppCurrency : ''
}

export const formatNumber = (number, format = '(0[.]000a)') => {
    const numbro = require('numbro');

    numbro.setDefaults({ thousandSeparated: true, })

    const num = numbro(number)

    if(! format) {
        return num.format()
    }

    return num.format(format)
}

export const animateRowItem = (id, animation = 'updated', callback = null) => {
    let rowItem = document.getElementById('item-' + id);

    if(! rowItem) { return; }
    
    rowItem.classList.remove(animation);
    
    setTimeout(() => {
        rowItem.classList.add(animation); 
        if(callback != null) {
            setTimeout(callback, 500);
        }
    }, 50);
}

export const cutString = (stringValue, upTo) => {
    if(stringValue.length > upTo) {
        return stringValue.substr(0, upTo) + '...'
    }

    return stringValue
}

export const colors = () => {
    return [
        {tailwind: 'bg-red-500', hex: '#ef4444'},
        {tailwind: 'bg-amber-500', hex: '#f59e0b'},
        {tailwind: 'bg-orange-500', hex: '#f97316'},
        {tailwind: 'bg-yellow-500', hex: '#eab308'},
        {tailwind: 'bg-green-500', hex: '#22c55e'},
        {tailwind: 'bg-lime-500', hex: '#84cc16'},
        {tailwind: 'bg-sky-500', hex: '#0ea5e9'},
        {tailwind: 'bg-teal-500', hex: '#14b8a6'},
        {tailwind: 'bg-blue-500', hex: '#3b82f6'},
        {tailwind: 'bg-indigo-500', hex: '#6366f1'},
        {tailwind: 'bg-fuchsia-500', hex: '#d946ef'},
        {tailwind: 'bg-pink-500', hex: '#ec4899'},
        {tailwind: 'bg-rose-500', hex: '#f43f5e'},
    ];
}

export const getTailwindColor = (index) => {
    return colors()[index] ? colors()[index].tailwind : "bg-gray-500";
}