import React, {useContext} from "react";
import { useState } from "react";
import {useNavigate} from "react-router-dom";
import {AuthContext} from "../context/AuthContext";

export default function LoginForm() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const navigate = useNavigate();
    const { user, login } = useContext(AuthContext);

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!email || !password){
            setError("Fill all fields");
            return;
        }
        try {
            const response = await fetch("/api/login_check", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"},
                body: JSON.stringify({ username: email, password }),
            });

            const data = await response.json();

            if (!response.ok) {
                console.log("Error:", data.error);

                setError(data.error || data.message || "Invalid credentials.");
                return;
            }

            await login(data.user, data.token)
            console.log('Zalogowano! Token:', data.token);
            navigate("/dashboard");
        }
        catch (error){
            setError(error.message);
            console.log("Response status:", response.status);
            console.log("Response data:", data);
        }
    };

    return (
        <>
            {error && (
                <div className="mb-4 bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-lg text-sm text-center mt-2">
                    {error}
                </div>
            )}
            <form onSubmit={handleSubmit} className="space-y-4">
                <label
                    htmlFor="email"
                    className="block text-sm font-medium text-ink mb-1"
                >Email
                </label>
                <input
                    className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                    type="email"
                    placeholder="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    required
                />
                <label
                    htmlFor="password"
                    className="block text-sm font-medium text-ink mb-1"
                >Password
                </label>
                <input
                    className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                    type="password"
                    placeholder="Password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    required
                />
                <div className="flex items-center justify-center mt-6">
                    <button type="submit"
                            className="cursor-pointer uppercase bg-cobalt text-white text-sm  px-8 py-2.5 rounded-lg hover:bg-cobalt-dark transition">Sign in
                    </button>
                </div>
            </form>
        </>
    );
}
