import { Link } from '@inertiajs/react';
import {
  SquaresFour,
  Receipt,
  Tag,
  FolderOpen,
  ChatCircleText,
  SignOut,
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

// Navigation items
const items = [
  {
    title: "Dashboard",
    url: "dashboard",
    icon: SquaresFour,
  },
  {
    title: "Transactions",
    url: "transactions",
    icon: Receipt,
  },
  {
    title: "Brands",
    url: "brands",
    icon: Tag,
  },
  {
    title: "Categories",
    url: "categories",
    icon: FolderOpen,
  },
  {
    title: "SMS Parser",
    url: "sms",
    icon: ChatCircleText,
  },
]

export function AppSidebar() {
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
                  <SidebarMenuButton  asChild isActive={route().current(item.url)}>
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
            <SidebarMenuButton asChild>
              <Link href={route('logout')} method="post" as="button">
                <SignOut />
                <span>Log Out</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>
    </Sidebar>
  )
}
