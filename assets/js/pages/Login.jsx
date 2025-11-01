import React, {useEffect} from "react";
import LoginForm from "../components/LoginForm";
import {LeftColumnContent} from "../components/LeftColumnContent";
import {Link} from "react-router-dom";

export default function Login() {
    useEffect(() => {
        document.title = "Log in - SpeakUp";
    }, []);

    return (
        <div className="bg-white md:bg-gradient-to-r from-paper to-white">
            <div className="max-w-screen-xl pt-15 px-4 lg:px-6 mx-auto grid grid-cols-1 md:grid-cols-2 min-h-screen">
                <LeftColumnContent />
                <div className="flex items-center justify-center md:justify-end py-8 sm:pl-8 bg-white">
                    <div className="w-full max-w-md">
                        <h1 className="text-3xl lg:text-4xl font-bold text-cobalt text-center mb-8">
                            Log in
                        </h1>
                        <LoginForm/>
                        <p className="mt-6 text-center text-sm text-ink">
                            Don't have an account?
                            <Link to="/register" className="text-cobalt font-medium font-semibold hover:underline ml-1">
                                Get started
                            </Link>
                        </p>
                        <p className="mt-6 text-center text-sm text-ink">
                            <a href="/reset-password"
                               className="text-cobalt font-medium font-semibold hover:underline">
                                Forgot your password?
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
