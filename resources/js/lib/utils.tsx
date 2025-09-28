import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

function cn(...inputs) {
  return twMerge(clsx(inputs));
}

// Export both named and default exports for maximum compatibility
export { cn };
export default cn;
