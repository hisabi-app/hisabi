import React, { useState, useEffect } from "react";

import Input from "@/Components/Global/Input";
import Label from "@/Components/Global/Label";
import SidePanel from '@/Components/Global/SidePanel';
import { updateSms } from "../../Api";

export default function Edit({sms, onClose, onUpdate}) {
    const [loading, setLoading] = useState(false);
    const [body, setBody] = useState('')

    useEffect(() => {
        if(! sms) return;

        setBody(sms.body)
    }, [sms])

    const update = () => {
        if(loading) { return }

        setLoading(true);

        updateSms({ id: sms.id, body })
            .then(({data}) => {
                setLoading(false);
                onUpdate(data.updateSms)
            })
            .catch(console.error);
    }
    
    return (
        <SidePanel toggleOpen={! sms ? false : true} 
                    onClose={onClose} 
                    title={"Fix SMS Parsing"}>
            <div className="p-1 rounded border-l-2 border-blue-500 pl-2 bg-blue-50">
                In order to make sure parsing this SMS is correct, please add the correspoding SMS template in the config file <span className="bg-blue-100 rounded px-1">config/finance.php</span> under <span className="bg-blue-100 rounded px-1">sms_templates</span>. <br/><br/> Once you finish, you can try to parse the SMS again. To learn more, please visit the <a className="underline" target="__blank" href="/docs/1.0/sms-parser">documentation</a>
            </div>
            {
                sms &&
                <div className="mt-6">
                    <div>
                        <Label forInput="body" value="Body" />

                        <Input
                            type="text"
                            name="body"
                            value={body}
                            className="mt-1 block w-full"
                            handleChange={(e) => setBody(e.target.value)}
                        />
                    </div>

                    <div className="flex items-center justify-end mt-4">
                        <button onClick={update} className="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-blue-500 transition ease-in-out duration-150">
                            {loading && <svg xmlns="http://www.w3.org/2000/svg" className="mr-2 animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>}
                            <span>Parse again</span>
                        </button>
                    </div>
                </div>
            }
        </SidePanel>
    )
  }