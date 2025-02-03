<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
    public function addDocument(Request $request)
    {
        try {
            $user = auth()->user();
            $employee = Employee::where('user_id', $user->id)->first();

            if (!$employee) {
                return $this->response(
                    false,
                    'Employee record not found for the user',
                    null,
                    404
                );
            }

            $validated = $request->validate([
                'doc_name' => 'required|string|max:255',
                'doc_type' => 'required|string|max:50',
                'doc_file' => 'required|file|mimes:pdf,zip,txt,doc,docx,xls,xlsx,jpg,png,jpeg|max:5120',
            ]);

            if (!$request->hasFile('doc_file')) {
                return $this->response(
                    false,
                    'No file uploaded',
                    null,
                    400
                );
            }

            $path = $request->file('doc_file')->store('documents', 'public');

            if (!$path) {
                return $this->response(
                    false,
                    'File storage failed',
                    null,
                    500
                );
            }
            //print_r($path);exit;

            $document = Document::create([
                'employee_id' => $employee->id,
                'doc_name' => $validated['doc_name'],
                'doc_type' => $validated['doc_type'],
                'doc_file' => $path,
            ]);

            return $this->response(
                true,
                'Document added successfully',
                $document,
                201
            );
        } catch (\Exception $e) {
            return $this->response(
                false,
                'Failed to add document',
                $e->getMessage(),
                500
            );
        }
    }


    public function deleteDocument($id)
    {
        try {
            // Find the document
            $document = Document::findOrFail($id);

            // Delete the file from storage
            if (\Storage::disk('public')->exists($document->doc_file)) {
                \Storage::disk('public')->delete($document->doc_file);
            }

            // Delete the document record
            $document->delete();

            // Return a success response
            return $this->response(
                true,
                'Document deleted successfully',
                null,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Failed to delete document',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get all documents for the logged-in user's employee.
     */
    public function getDocuments($userId)
    {
        //print_r($userId);exit;
        try {
            // Find the employee associated with the provided user ID
            $employee = Employee::where('user_id', $userId)->first();

            if (!$employee) {
                return $this->response(
                    false,
                    'Employee record not found for the given user ID',
                    null,
                    404
                );
            }

            // Get all documents for the employee
            $documents = Document::where('employee_id', $employee->id)->get();

            // Add the full URL to the doc_file attribute
            foreach ($documents as $document) {
                $document->doc_file = url('storage/' . $document->doc_file);
            }

            // Return the documents in the response
            return $this->response(
                true,
                'Documents retrieved successfully',
                $documents,
                200
            );
        } catch (\Exception $e) {
            // Handle any exceptions
            return $this->response(
                false,
                'Failed to retrieve documents',
                $e->getMessage(),
                500
            );
        }
    }


    // Download Documents
    public function downloadDocument($id)
    {
        try {
            $document = Document::find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Extract the file path from the full URL stored in doc_file
            $url = $document->doc_file;

            // Use parse_url to get the path part of the URL
            $parsedUrl = parse_url($url);
            $filePath = ltrim($parsedUrl['path'], '/'); // Remove the leading slash

            // Construct the local file path by adding it to storage path
            $localFilePath = storage_path('app/public/' . $filePath);

            // Check if the file exists
            if (!file_exists($localFilePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on the server'
                ], 404);
            }

            // Return the file for download
            return response()->download($localFilePath);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document',
                'error' => $e->getMessage()
            ], 500);
        }
    }






}
