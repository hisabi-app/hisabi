import React, { useState } from "react";

import Label from "@/Components/Global/Label";
import SidePanel from '@/Components/Global/SidePanel';
import { createSms } from "../../Api";

export default function Create({showCreate, onClose, onCreate}) {
    const [sms, setSms] = useState('')

    const create = () => {
        createSms({
            sms
        })
        .then(({data}) => {
            onCreate(data.createSms)
            setSms('')
        })
        .catch(console.error);
    }
    
    let templatesString = "Available templates\n" + AppSmsTemplates;

    return (
        <SidePanel toggleOpen={showCreate} 
                    onClose={onClose}
                    title={"Parse SMS"}>
            <div>
                <div>
                    <Label forInput="sms" value="SMS Message(s)" />
                    <small className="text-gray-500">You can parse multiple messages one per line</small>

                    <textarea className="mt-2 border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        name="sms"
                        rows={10}
                        placeholder={templatesString}
                        onChange={(e) => setSms(e.target.value)}
                    ></textarea>
                </div>

                <div className="flex items-center justify-end mt-4">
                    {sms && <button onClick={create} className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                        Parse
                    </button>}
                </div>
            </div>
        </SidePanel>
    )
  }