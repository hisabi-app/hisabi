import { useState } from 'react';
import { Brain, Notebook as NotebookIcon, ChatCircleText } from '@phosphor-icons/react';
import {
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
} from '@/components/ui/sidebar';
import HisabiGPT from './Global/HisabiGPT';
import Notebook from './Global/Notebook';
import SmsParser from './Global/SmsParser';

export default function RightSidebar() {
  const [activePanel, setActivePanel] = useState<'ai' | 'notebook' | 'sms' | null>(null);

  const togglePanel = (panel: 'ai' | 'notebook' | 'sms') => {
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
              <Brain size={18} />
              <span 
                className="font-medium whitespace-nowrap"
                style={{ writingMode: 'vertical-lr', transform: 'rotate(0deg)' }}
              >
                Hisabi AI
              </span>
            </SidebarMenuButton>
          </SidebarMenuItem>
          
          <SidebarMenuItem>
            <SidebarMenuButton
              onClick={() => togglePanel('notebook')}
              isActive={activePanel === 'notebook'}
              size="sm"
              className="flex flex-col items-center gap-1 h-auto py-3"
            >
              <NotebookIcon size={18} />
              <span 
                className="font-medium whitespace-nowrap"
                style={{ writingMode: 'vertical-lr', transform: 'rotate(0deg)' }}
              >
                Notebook
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
              <ChatCircleText size={18} />
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
        {activePanel === 'ai' && <HisabiGPT onClose={() => setActivePanel(null)} />}
        {activePanel === 'notebook' && <Notebook onClose={() => setActivePanel(null)} />}
        {activePanel === 'sms' && <SmsParser onClose={() => setActivePanel(null)} />}
      </div>
    </div>
  );
}

