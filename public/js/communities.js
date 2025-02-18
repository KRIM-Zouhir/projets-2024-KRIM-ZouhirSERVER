// public/js/admin/communities.js
async function toggleArchiveStatus(communityId, shouldArchive) {
    if (!confirm(`Are you sure you want to ${shouldArchive ? 'archive' : 'activate'} this community?`)) {
        return;
    }

    try {
        const response = await fetch(`/moderator/community/${communityId}/toggle-archive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ archived: shouldArchive })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        window.location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update community status. Please try again.');
    }
}

// public/js/admin/discussions.js
async function toggleDiscussionLock(discussionId, shouldLock) {
    if (!confirm(`Are you sure you want to ${shouldLock ? 'lock' : 'unlock'} this discussion?`)) {
        return;
    }

    try {
        const response = await fetch(`/moderator/discussion/${discussionId}/toggle-lock`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ locked: shouldLock })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        window.location.reload();
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update discussion status. Please try again.');
    }
}