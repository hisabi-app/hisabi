import { useState } from 'react'
import { CheckIcon, SelectorIcon } from '@heroicons/react/solid'
import { Combobox } from '@headlessui/react'

function classNames(...classes) {
  return classes.filter(Boolean).join(' ')
}

export default function ComboboxComponent({label, items, initialSelectedItem, onChange, displayInputValue, displayOptionValue}) {
  const [query, setQuery] = useState('')
  const [selectedItem, setSelectedItem] = useState(initialSelectedItem)

  const filteredItems =
    query === ''
      ? items
      : items.filter((item) => {
          return item.name.toLowerCase().includes(query.toLowerCase())
        })
    
  return (
    <Combobox as="div" value={selectedItem} onChange={(item) => {
        setSelectedItem(item)
        onChange(item)
    }}>
      <Combobox.Label className="block text-sm font-medium text-gray-700">{label}</Combobox.Label>
      <div className="relative mt-1">
        <Combobox.Input
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-3 pr-10 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 sm:text-sm"
          onChange={(event) => setQuery(event.target.value)}
          displayValue={(item) => displayInputValue ? displayInputValue(item) : item?.name}
        />
        <Combobox.Button className="absolute inset-y-0 right-0 flex items-center rounded-r-md px-2 focus:outline-none">
          <SelectorIcon className="h-5 w-5 text-gray-400" aria-hidden="true" />
        </Combobox.Button>

        {filteredItems.length > 0 && (
          <Combobox.Options className="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
            {filteredItems.map((item) => (
              <Combobox.Option
                key={item.id}
                value={item}
                className={({ active }) =>
                  classNames(
                    'relative cursor-default select-none py-2 pl-8 pr-4',
                    active ? 'bg-blue-500 text-white' : 'text-gray-900'
                  )
                }
              >
                {({ active }) => (
                  <>
                    <span className={classNames('block truncate', item.id == selectedItem?.id && 'font-semibold')}>{displayOptionValue ? displayOptionValue(item) : item?.name}</span>

                    {item.id == selectedItem?.id && (
                      <span
                        className={classNames(
                          'absolute inset-y-0 left-0 flex items-center pl-1.5',
                          active ? 'text-white' : 'text-blue-500'
                        )}
                      >
                        <CheckIcon className="h-5 w-5" aria-hidden="true" />
                      </span>
                    )}
                  </>
                )}
              </Combobox.Option>
            ))}
          </Combobox.Options>
        )}
      </div>
    </Combobox>
  )
}