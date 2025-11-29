import { useState } from 'react';
import { SparkleIcon, ChatCircleTextIcon } from '@phosphor-icons/react';
import {
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
} from '@/components/ui/sidebar';
import HisabiAIChat from './Global/HisabiAIChat';
import SmsParser from './Global/SmsParser';

export default function RightSidebar() {
  const [activePanel, setActivePanel] = useState<'ai' | 'sms' | null>(null);

  const togglePanel = (panel: 'ai' | 'sms') => {
    if (activePanel === panel) {
      setActivePanel(null);
    } else {
      setActivePanel(panel);
    }
  };

  return (
    <div className="hidden md:flex h-screen bg-sidebar">
      {/* Narrow sidebar with vertical labels */}
      <div className="w-12 flex-shrink-0 bg-sidebar flex flex-col items-center pr-2 py-2 gap-1">
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton
              onClick={() => togglePanel('ai')}
              isActive={activePanel === 'ai'}
              size="sm"
              className="flex flex-col items-center gap-1 h-auto py-3"
            >
              <SparkleIcon size={18} />
              <span 
                className="font-medium whitespace-nowrap"
                style={{ writingMode: 'vertical-lr', transform: 'rotate(0deg)' }}
              >
                Hisabi AI (BETA)
              </span>
            </SidebarMenuButton>
          </SidebarMenuItem>
                    
          <SidebarMenuItem>
            <SidebarMenuButton
              onClick={() => togglePanel('sms')}
              isActive={activePanel === 'sms'}
              size="sm"
              className="flex flex-col items-center gap-1 h-auto py-3"
            >
              <ChatCircleTextIcon size={18} />
              <span
                className="font-medium whitespace-nowrap"
                style={{ writingMode: 'vertical-lr', transform: 'rotate(0deg)' }}
              >
                SMS Parser
              </span>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </div>

      {/* Expandable content panel */}
      <div className={`overflow-hidden transition-all duration-300 ease-in-out border-l ${
        activePanel ? 'w-[400px]' : 'w-0'
      }`}>
        {activePanel === 'ai' && <HisabiAIChat onClose={() => setActivePanel(null)} />}
        {activePanel === 'sms' && <SmsParser onClose={() => setActivePanel(null)} />}
      </div>
    </div>
  );
}

