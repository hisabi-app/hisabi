import { useState } from "react";
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SidePanel from '@/components/Global/SidePanel';
import { createSms } from "../../Api";
import { Button } from "@/components/ui/button";

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

    return (
        <SidePanel toggleOpen={showCreate}
                    onClose={onClose}
                    title={"Parse SMS"}>
            <div>
                <div>
                    <Label htmlFor="sms">
                        SMS Message(s)
                    </Label>
                    <small className="text-gray-500">You can parse multiple messages one per line</small>

                    <textarea className="mt-2 border-gray-300 w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        name="sms"
                        rows={10}
                        onChange={(e) => setSms(e.target.value)}
                    ></textarea>
                </div>

                {! showDefaultDateForm && <div>
                    <small className="text-gray-500">Click <button onClick={() => setShowDefaultDateForm(true)} className="text-blue-500">here</button> if you want to set a default date for transactions</small>
                </div>}

                {showDefaultDateForm && <div className="mt-4">
                    <Label htmlFor="date">
                        Default Transaction(s) Date
                    </Label>

                    <Input
                        type="date"
                        name="date"
                        value={createdAt}
                        className="mt-1 block w-full"
                        onChange={(e) => setCreatedAt(e.target.value)}
                    />
                </div>}
                <div className="flex items-center justify-end mt-4">
                    {sms && <Button onClick={create}>
                        Parse
                    </Button>}
                </div>
            </div>
        </SidePanel>
    )
  }
