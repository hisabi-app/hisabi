import * as React from "react"
import { format, subDays } from "date-fns"
import { CalendarIcon } from "@phosphor-icons/react"
import { DateRange, Matcher } from "react-day-picker"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover"

interface DatePickerWithRangeProps extends React.HTMLAttributes<HTMLDivElement> {
  onDateChange?: (date: DateRange | undefined) => void
  disabled?: Matcher | Matcher[] | undefined;
  initialDate?: DateRange | undefined;
}

export function DatePickerWithRange({
  className,
  onDateChange,
  disabled,
  initialDate,
  ...props
}: DatePickerWithRangeProps) {
  const [date, setDate] = React.useState<DateRange | undefined>(
    initialDate || {
      from: subDays(new Date(), 30),
      to: new Date(),
    }
  )

  const handleDateChange = (newDate: DateRange | undefined) => {
    setDate(newDate)
    onDateChange?.(newDate)
  }

  const presets = [
    {
      label: 'Last 7 days',
      value: '7',
      dateRange: {
        from: subDays(new Date(), 7),
        to: new Date(),
      },
    },
    {
      label: 'Last 30 days',
      value: '30',
      dateRange: {
        from: subDays(new Date(), 30),
        to: new Date(),
      },
    },
    {
      label: 'Last 90 days',
      value: '90',
      dateRange: {
        from: subDays(new Date(), 90),
        to: new Date(),
      },
    },
  ]

  return (
    <div className={cn("grid gap-2", className)} {...props}>
      <Popover>
        <PopoverTrigger asChild>
          <Button
            id="date"
            variant={"outline"}
            className={cn(
              "justify-start text-left font-normal",
              !date && "text-muted-foreground"
            )}
          >
            <CalendarIcon className="mr-2 h-4 w-4" />
            {date?.from ? (
              date.to ? (
                <>
                  {format(date.from, "LLL dd, y")} -{" "}
                  {format(date.to, "LLL dd, y")}
                </>
              ) : (
                format(date.from, "LLL dd, y")
              )
            ) : (
              <span>Pick a date</span>
            )}
          </Button>
        </PopoverTrigger>
        <PopoverContent className="w-auto p-0" align="start">
          <div className="border-b border-border p-3">
            <div className="flex space-x-2">
              {presets.map((preset) => (
                <Button
                  key={preset.value}
                  onClick={() => handleDateChange(preset.dateRange)}
                  variant="outline"
                  size="sm"
                  className={cn(
                    "flex-1",
                    date?.from === preset.dateRange.from && date?.to === preset.dateRange.to && "bg-primary text-primary-foreground hover:bg-primary/90"
                  )}
                >
                  {preset.label}
                </Button>
              ))}
            </div>
          </div>
          <Calendar
            initialFocus
            mode="range"
            defaultMonth={date?.from}
            selected={date}
            onSelect={handleDateChange}
            numberOfMonths={2}
            disabled={disabled}
          />
        </PopoverContent>
      </Popover>
    </div>
  )
} 