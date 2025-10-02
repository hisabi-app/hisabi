import { useState, useEffect, forwardRef } from 'react'
import { CheckIcon, ChevronsUpDownIcon } from 'lucide-react'

import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Label } from '@/components/ui/label'

interface ComboboxProps {
  label?: string
  items: any[]
  initialSelectedItem?: any
  onChange: (item: any) => void
  displayInputValue?: (item: any) => string
  displayOptionValue?: (item: any) => string
}

const ComboboxComponent = forwardRef<HTMLDivElement, ComboboxProps>(
  function ComboboxComponent(
    { label, items, initialSelectedItem, onChange, displayInputValue, displayOptionValue },
    ref
  ) {
    const [open, setOpen] = useState(false)
    const [selectedItem, setSelectedItem] = useState(initialSelectedItem)

    // Sync selectedItem when initialSelectedItem changes
    useEffect(() => {
      setSelectedItem(initialSelectedItem)
    }, [initialSelectedItem])

    const handleSelect = (item: any) => {
      setSelectedItem(item)
      onChange(item)
      setOpen(false)
    }

    const getDisplayValue = (item: any) => {
      if (!item) return ''
      if (displayInputValue) return displayInputValue(item)
      return item.name
    }

    const getOptionValue = (item: any) => {
      if (!item) return ''
      if (displayOptionValue) return displayOptionValue(item)
      return item.name
    }

    return (
      <div ref={ref}>
        {label && (
          <Label className="text-sm text-foreground">
            {label}
          </Label>
        )}
        <Popover open={open} onOpenChange={setOpen}>
          <PopoverTrigger className="w-full">
            <Button
              variant="outline"
              role="combobox"
              aria-expanded={open}
              className="w-full justify-between font-normal"
            >
              <span className={cn(
                "truncate",
                !selectedItem && "text-muted-foreground"
              )}>
                {selectedItem
                  ? getDisplayValue(selectedItem)
                  : 'Select...'}
              </span>
              <ChevronsUpDownIcon className="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
          </PopoverTrigger>
          <PopoverContent className="p-0" align="start" style={{ width: 'var(--radix-popover-trigger-width)' }}>
            <Command>
              <CommandInput placeholder="Search..." />
              <CommandList>
                <CommandEmpty>No results found.</CommandEmpty>
                <CommandGroup>
                  {items.map((item) => (
                    <CommandItem
                      key={item.id}
                      value={getOptionValue(item)}
                      onSelect={() => handleSelect(item)}
                    >
                      <CheckIcon
                        className={cn(
                          "mr-2 h-4 w-4",
                          selectedItem?.id === item.id ? "opacity-100" : "opacity-0"
                        )}
                      />
                      {getOptionValue(item)}
                    </CommandItem>
                  ))}
                </CommandGroup>
              </CommandList>
            </Command>
          </PopoverContent>
        </Popover>
      </div>
    )
  }
)

export default ComboboxComponent
