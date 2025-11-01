import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import RegistrationForm from "../components/RegistrationForm";
import { LeftColumnContent } from "../components/LeftColumnContent";

const roleLabels = {
    student: "Student",
    teacher: "Teacher",
};

export default function Register() {
    const [role, setRole] = useState("student");
    const navigate = useNavigate();

    useEffect(() => {
        document.title = `Register as ${roleLabels[role]} - SpeakUp`;
    }, [role]);

    const handleSuccess = () => {
        navigate("/register/check-email");
    };

    return (
        <div className="bg-white md:bg-gradient-to-r from-paper to-white">
            <div className="max-w-screen-xl pt-15 px-4 lg:px-6 mx-auto grid grid-cols-1 md:grid-cols-2 min-h-screen">
                <LeftColumnContent />
                <div className="flex items-center justify-center md:justify-end py-8 sm:pl-8 bg-white">
                    <div className="w-full max-w-md">
                        <div className="flex justify-center gap-3 mb-6" role="tablist" aria-label="Registration type">
                            {Object.entries(roleLabels).map(([key, label]) => (
                                <button
                                    key={key}
                                    type="button"
                                    onClick={() => setRole(key)}
                                    className={`cursor-pointer px-4 py-2 rounded-lg border text-sm font-medium transition ${
                                        role === key
                                            ? "bg-cobalt text-white border-cobalt"
                                            : "border-gray-200 text-ink/80 hover:border-cobalt hover:text-cobalt"
                                    }`}
                                    aria-pressed={role === key}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                        <h1 className="text-3xl lg:text-4xl font-bold text-cobalt text-center mb-6">
                            Register as a {roleLabels[role]}
                        </h1>
                        <RegistrationForm role={role} onSuccess={handleSuccess} />
                        <p className="mt-6 text-center text-sm text-ink/70">
                            Already have an account?
                            <Link to="/login" className="text-cobalt font-medium font-semibold hover:underline ml-1">
                                Log in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}