import  React from "react";

export function LeftColumnContent() {
    return (
        <div className="hidden md:flex flex-col justify-center py-12 pr-8">
            <span className="text-5xl font-bold text-cobalt mb-4">SpeakUp</span>
            <p className="text-lg text-ink mb-6">Learn languages with real teachers</p>
            <img src="/images/language-learning.svg" alt="Language Learning"
                 className="-mt-8 max-w-8/10 h-auto"/>
        </div>
    )
}