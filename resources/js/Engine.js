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
}