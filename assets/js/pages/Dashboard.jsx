import React, {useContext, useEffect} from "react";
import {LeftColumnContent} from "../components/LeftColumnContent";
import LoginForm from "../components/LoginForm";
import {AuthContext} from "../context/AuthContext";

export default function Dashboard() {
    const { user, token } = useContext(AuthContext);

    useEffect(() => {
        document.title = "Dashboard - SpeakUp";
    }, []);

    return (
        <div className="bg-paper">
            <div className="max-w-screen-xl pt-15 px-4 lg:px-6 mx-auto grid grid-cols-1 md:grid-cols-2 min-h-screen">
                <div className="p-10">
                    <h1 className="text-3xl font-bold text-cobalt">Welcome, {user.name} {user.lastName}!</h1>
                    <p className="mt-4">Your JWT token:</p>
                    <pre className="mt-2 p-4 bg-gray-100 rounded text-wrap break-all">{token}</pre>
                </div>
            </div>
        </div>
    );
}


