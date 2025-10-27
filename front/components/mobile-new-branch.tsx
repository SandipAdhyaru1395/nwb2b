"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
    faChevronLeft,
    faCircleCheck,
    faCircleXmark,
    faGauge,
    faShop,
    faUser,
    faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { Banner } from "./banner";
import FloatingInput from "./ui/floating-input";

interface MobileNewBranchProps {
    onNavigate: (
        page:
            | "dashboard"
            | "shop"
            | "basket"
            | "wallet"
            | "account"
            | "rep-details"
            | "company-details"
            | "branches"
    ) => void;
    onBack: () => void;
}

export function MobileNewBranch({ onNavigate, onBack }: MobileNewBranchProps) {
    const [branch, setBranch] = useState({
        name: "",
        line1: "",
        line2: "",
        city: "",
        county: "",
        postcode: "",
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setBranch((prev) => ({ ...prev, [name]: value }));
    };

    const handleSave = () => {
        console.log("Saving new branch:", branch);
        // TODO: Send branch to API or store locally
        onBack();
    };

    return (
        <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
            {/* Header */}
            <div className="bg-white flex items-center border-b h-[50px]">
                <div className="flex items-center">
                    <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
                        <FontAwesomeIcon icon={faShop} className="text-green-600" style={{ width: "27px", height: "24px" }} />
                    </div>
                    <span onClick={() => onNavigate("account")} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline leading-[16px]">Account</span>
                    &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
                    &nbsp;<span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline leading-[16px]">My Branches</span>
                    &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
                    &nbsp;<span className="text-[16px] font-semibold">New Branch</span>
                </div>
            </div>
            {/* Banner */}
            <Banner />
            {/* Content */}
            <div className="p-[10px] mb-[82px]">
                <FloatingInput
                    label="Branch Name"
                    placeholder="Please enter your branch name..."
                />
                <hr className="my-[20px]"></hr>
                <FloatingInput
                    label="Line 1"
                    placeholder="Please enter address line 1..."
                />

                <FloatingInput
                    label="Line 2"
                    placeholder="Please enter address line 2..."
                />

                <FloatingInput
                    label="City"
                    placeholder="Please enter address city..."
                />

                <FloatingInput
                    label="Country"
                    placeholder="Please enter address country..."
                />

                <FloatingInput
                    label="Postcode"
                    placeholder="Please enter address postcode..."
                />
                <hr className="my-[20px]"></hr>
                {/* Buttons */}
                <Button
                    onClick={handleSave}
                    className="w-full cursor-pointer rounded bg-green-600 hover:bg-green-700 text-white font-semibold h-[45px] !leading-[13px]"
                >
                    <div className="!leading-[13px]">
                        <FontAwesomeIcon  icon={faCircleCheck} style={{ width: "16px", height: "16px" }} />
                    </div>
                    <span className="text-[16px]">Save</span>
                </Button>
            </div>

            {/* Bottom Navigation */}
            <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
                <div className="flex flex-row items-center justify-between h-[72px]">
                    <button
                        onClick={() => onNavigate("dashboard")}
                        className="flex flex-col items-center text-[#607565]"
                    >
                        <FontAwesomeIcon icon={faGauge} style={{ width: "24px", height: "24px" }} />
                        <span className="text-xs mt-[5px]">Dashboard</span>
                    </button>
                    <button
                        onClick={() => onNavigate("shop")}
                        className="flex flex-col items-center text-[#607565]"
                    >
                        <FontAwesomeIcon icon={faShop} style={{ width: "30px", height: "24px" }} />
                        <span className="text-xs mt-[5px]">Shop</span>
                    </button>
                    <button
                        onClick={() => onNavigate("wallet")}
                        className="flex flex-col items-center text-[#607565]"
                    >
                        <FontAwesomeIcon icon={faWallet} style={{ width: "24px", height: "24px" }} />
                        <span className="text-xs mt-[5px]">Wallet</span>
                    </button>
                    <button
                        onClick={() => onNavigate("account")}
                        className="flex flex-col items-center text-[#607565]"
                    >
                        <FontAwesomeIcon icon={faUser} style={{ width: "21px", height: "24px" }} />
                        <span className="text-xs mt-[5px]">Account</span>
                    </button>
                </div>
            </nav>
        </div>
    );
}
