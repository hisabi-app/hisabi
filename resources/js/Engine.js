import numbro from 'numbro'

numbro.setDefaults({ thousandSeparated: true, })

export default class Engine {
    formatNumber(number, format = '(0[.]00a)') {
        const num = numbro(number)

        if(! format) {
            return num.format()
        }
    
        return num.format(format)
    }

    animateRowItem(id, animation = 'updated', callback = null) {
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

    cutString(stringValue, upTo) {
        if(stringValue.length > upTo) {
            return stringValue.substr(0, upTo) + '...'
        }

        return stringValue
    }
}