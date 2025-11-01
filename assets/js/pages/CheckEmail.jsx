import React, { useEffect } from "react";
import { Link } from "react-router-dom";
import { LeftColumnContent } from "../components/LeftColumnContent";

export default function CheckEmail() {
    useEffect(() => {
        document.title = "Check your email - SpeakUp";
    }, []);

    return (
        <div className="bg-white md:bg-gradient-to-r from-paper to-white">
            <div className="max-w-screen-xl pt-15 px-4 lg:px-6 mx-auto grid grid-cols-1 md:grid-cols-2 min-h-screen">
                <LeftColumnContent />
                <div className="flex items-center justify-center md:justify-end py-8 sm:pl-8 bg-white">
                    <div className="w-full max-w-md text-center">
                        <h1 className="text-3xl lg:text-4xl font-bold text-cobalt mb-4">Thank you for registering!</h1>
                        <p className="text-sm text-ink/80 mb-8">
                            We sent you a confirmation email. Please check your inbox and follow the instructions to finish setting up your
                            account.
                        </p>
                        <Link
                            to="/login"
                            className="inline-flex justify-center cursor-pointer uppercase bg-cobalt text-white text-sm px-8 py-2.5 rounded-lg hover:bg-cobalt-dark transition"
                        >
                            Back to login
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}