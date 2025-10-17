"use client";

import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
  faChevronLeft,
  faCircleCheck,
  faCircleXmark,
  faGauge,
  faShop,
  faStore,
  faUser,
  faWallet,
} from "@fortawesome/free-solid-svg-icons";
import { Banner } from "./banner";
import FloatingInput from "./ui/floating-input";

interface Branch {
  id: number;
  name: string;
  address_line1: string;
  address_line2: string;
  city: string;
  zip_code: string;
  country: string;
}

interface MobileEditBranchProps {
  branchDetails: Branch;
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

export function MobileEditBranch({ branchDetails, onNavigate, onBack }: MobileEditBranchProps) {
  const [branch, setBranch] = useState(branchDetails);

  // ✅ Simulate fetching branch data (replace with real API call)
  useEffect(() => {
    // setBranch({
    //   id: 1,
    //   name: "Downtown Branch",
    //   address_line1: "123 Main Street",
    //   address_line2: "Suite 4B",
    //   city: "Mumbai",
    //   country: "Maharashtra",
    //   zip_code: "400001"
    // });
  }, [branchDetails]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setBranch((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = () => {
    // console.log("Saving branch:", branchId, branch);
    // TODO: PUT request to /api/branches/:id
    onBack();
  };

  const handleDelete = () => {
    if (confirm("Are you sure you want to delete this branch?")) {
      // console.log("Deleting branch:", branchId);
      // TODO: DELETE request to /api/branches/:id
      onBack();
    }
  };

  return (
    <div className="w-full max-w-[1000px] mx-auto bg-white min-h-screen">
      {/* Header */}
      <div className="bg-white flex items-center border-b h-[50px]">
        <div className="flex items-center">
          <div className="w-[66px] h-[25px] rounded-full flex items-center justify-center">
            <FontAwesomeIcon icon={faStore} className="text-green-600" style={{ width: "27px", height: "24px" }} />
          </div>
          <span onClick={onBack} className="text-sm text-[#ccc] text-[12px] hover:cursor-pointer hover:underline">Account</span>
          &nbsp;<span className="text-sm text-[#ccc] text-[12px]"> /</span>
          &nbsp;<span className="text-[16px] font-semibold">Branch</span>
        </div>
      </div>

      {/* Banner */}
      <Banner />

      {/* Form */}
      <div className="p-[10px] mb-[82px]">
        <FloatingInput
          label="Branch Name"
          name="name"
          value={branch.name}
          onChange={handleChange}
          placeholder="Enter branch name..."
        />
        <hr className="my-[20px]" />

        <FloatingInput
          label="Line 1"
          name="line1"
          value={branch.address_line1}
          onChange={handleChange}
          placeholder="Enter address line 1..."
        />

        <FloatingInput
          label="Line 2"
          name="line2"
          value={branch.address_line2}
          onChange={handleChange}
          placeholder="Enter address line 2..."
        />

        <FloatingInput
          label="City"
          name="city"
          value={branch.city}
          onChange={handleChange}
          placeholder="Enter city..."
        />

        <FloatingInput
          label="Country"
          name="county"
          value={branch.country}
          onChange={handleChange}
          placeholder="Enter country..."
        />

        <FloatingInput
          label="Postcode"
          name="postcode"
          value={branch.zip_code}
          onChange={handleChange}
          placeholder="Enter postcode..."
        />

        <hr className="my-[20px]" />

        {/* Buttons */}
        <Button
          onClick={handleSave}
          className="w-full cursor-pointer rounded bg-green-600 hover:bg-green-700 text-white font-semibold h-[45px] !leading-[13px]"
        >
          <div className="!leading-[13px]">
            <FontAwesomeIcon icon={faCircleCheck} style={{ width: "16px", height: "16px" }} />
          </div>
          <span className="text-[16px]">Save</span>
        </Button>
        <hr className="my-[20px]"></hr>
        <Button
          onClick={handleDelete}
          className="w-full cursor-pointer rounded bg-red-600 hover:bg-red-700 text-white font-semibold h-[45px] !leading-[13px]"
        >
          <div className="!leading-[13px]">
            <FontAwesomeIcon icon={faCircleXmark} style={{ width: "16px", height: "16px" }} />
          </div>
          <span className="text-[16px]">Delete</span>
        </Button>
      </div>

      {/* Bottom Navigation */}
      <nav className="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-full max-w-[1000px] bg-white border-t z-50 px-[18px]">
        <div className="flex flex-row items-center justify-between h-[72px] footer-nav-col">
          <button onClick={() => onNavigate("dashboard")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faGauge} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Dashboard</span>
          </button>
          <button onClick={() => onNavigate("shop")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faShop} className="text-[#607565]" style={{ width: "30px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Shop</span>
          </button>
          <button onClick={() => onNavigate("wallet")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faWallet} className="text-[#607565]" style={{ width: "24px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Wallet</span>
          </button>
          <button onClick={() => onNavigate("account")} className="flex flex-col items-center text-[#607565] hover:text-[#607565] hover:cursor-pointer w-[192px]">
            <FontAwesomeIcon icon={faUser} className="text-[#607565]" style={{ width: "21px", height: "24px" }} />
            <span className="text-xs mt-[5px]">Account</span>
          </button>
        </div>
      </nav>
    </div>
  );
}
