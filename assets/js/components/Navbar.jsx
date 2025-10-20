import React, {useContext} from "react";
import { Link, useNavigate } from "react-router-dom";
import {AuthContext} from "../context/AuthContext";

export default function Navbar() {
    const navigate = useNavigate();
    const { user, logout } = useContext(AuthContext);

    return (
        <nav className="fixed right-0 left-0 bg-white border-gray-200 px-4 lg:px-6 py-2.5 shadow-sm z-100">
            <div className="flex flex-wrap items-center justify-between max-w-screen-xl mx-auto">
                <Link to="/" className="flex items-center">
                    <span className="self-center text-xl font-semibold whitespace-nowrap ">
                        SpeakUp
                    </span>
                </Link>
                <div className="flex items-center lg:order-2">
                    {user ? (
                        <>
                            <div className="text-ink mr-4 text-sm">
                                Hello, {user.name} {user.lastName} ({user.type})!
                            </div>
                            <button
                                onClick={logout}
                                className="cursor-pointer text-white bg-cobalt hover:bg-cobalt-dark focus:ring-2 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 transition"
                            >
                                Logout
                            </button>
                        </>
                    ) : (
                        <>
                            <Link
                                to="/login"
                                className="text-ink shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cobalt font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 mr-2 transition"
                            >
                                Log in
                            </Link>
                            <Link
                                to="/register"
                                className="text-white bg-cobalt hover:bg-cobalt-dark focus:ring-2 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 transition"
                            >
                                Get started
                            </Link>
                        </>
                    )}
                </div>

                <div
                    className="items-center justify-between hidden w-full lg:flex lg:w-auto lg:order-1"
                    id="mobile-menu-2"
                >
                    <ul className="flex flex-col mt-4 font-medium lg:flex-row lg:space-x-8 lg:mt-0"></ul>
                </div>
            </div>
        </nav>
    );
}
