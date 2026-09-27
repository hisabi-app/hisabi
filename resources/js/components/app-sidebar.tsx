import { useState } from 'react';
import { Link } from '@inertiajs/react';
import {
  Receipt,
  StorefrontIcon,
  CirclesThreeIcon,
  ChartDonutIcon,
  WalletIcon,
  ChatCircleTextIcon
} from "@phosphor-icons/react"

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar"
import ApplicationLogo from "@/components/Global/ApplicationLogo"
import { UserNav } from "@/components/user-nav"
import SmsParser from "@/components/Global/SmsParser"

// Navigation items
const items = [
  {
    title: "Insights",
    url: "insights",
    icon: ChartDonutIcon,
  },
  {
    title: "Transactions",
    url: "transactions",
    icon: Receipt,
  },
  {
    title: "Brands",
    url: "brands",
    icon: StorefrontIcon,
  },
  {
    title: "Categories",
    url: "categories",
    icon: CirclesThreeIcon,
  },
  {
    title: "Budgets",
    url: "budgets",
    icon: WalletIcon,
  },
]

interface AppSidebarProps {
  auth?: {
    user: {
      name: string;
      email: string;
    };
  };
}

export function AppSidebar({ auth }: AppSidebarProps) {
  const [smsParserOpen, setSmsParserOpen] = useState(false)

  return (
    <Sidebar collapsible="offcanvas" variant="inset">
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/">
                <ApplicationLogo />
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupContent>
            <SidebarMenu>
              {items.map((item) => (
                <SidebarMenuItem key={item.title}>
                  <SidebarMenuButton asChild isActive={route().current(item.url)}>
                    <Link href={route(item.url)}>
                      <item.icon />
                      <span>{item.title}</span>
                    </Link>
                  </SidebarMenuButton>
                </SidebarMenuItem>
              ))}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>
      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton onClick={() => setSmsParserOpen(true)}>
              <ChatCircleTextIcon />
              <span>SMS Parser</span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
        {auth?.user && <UserNav user={auth.user} />}
      </SidebarFooter>
      <SmsParser open={smsParserOpen} onOpenChange={setSmsParserOpen} />
    </Sidebar>
  )
}
