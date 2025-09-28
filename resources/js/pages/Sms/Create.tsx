import React, { useState } from "react";

import Label from "@/Components/Global/Label";
import SidePanel from '@/Components/Global/SidePanel';
import { createSms } from "../../Api";
import Input from "@/Components/Global/Input";

export default function Create({showCreate, onClose, onCreate}) {
    const [sms, setSms] = useState('');
    const [showDefaultDateForm, setShowDefaultDateForm] = useState(false);
    const [createdAt, setCreatedAt] = useState(null);

    const create = () => {
        createSms({
            sms,
            createdAt
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

                {! showDefaultDateForm && <div>
                    <small className="text-gray-500">Click <button onClick={() => setShowDefaultDateForm(true)} className="text-blue-500">here</button> if you want to set a default date for transactions</small>
                </div>}

                {showDefaultDateForm && <div className="mt-4">
                    <Label forInput="date" value="Default Transaction(s) Date" />

                    <Input
                        type="date"
                        name="date"
                        value={createdAt}
                        className="mt-1 block w-full"
                        handleChange={(e) => setCreatedAt(e.target.value)}
                    />
                </div>}

                <div className="flex items-center justify-end mt-4">
                    {sms && <button onClick={create} className="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-green-500 transition ease-in-out duration-150">
                        Parse
                    </button>}
                </div>
            </div>
        </SidePanel>
    )
  }
