import { isSessionExists, xswpNodeSocketSet,createSession, getSession, deleteSession } from './../../whatsapp.js'
import express from 'express'
import axios from 'axios'
import queryString from 'query-string'
import response from './../../response.js'
import { rmSync, readdir, readFile, readFileSync } from 'fs'

const getxscode = (req, res) => {
      readdir('storage/sessions/md_'+res.locals.sessionId, (err, files) => {
          if (err){
            response(res, 403, false, 'Session not found. ERR:: 01')
          } else {
            if(files.length>0){
                response(res, 200, true, 'Session found.')
            }else{
                response(res, 403, true, 'Session not found.')
            }
          }
      })
}

const status = (req, res) => {
    let message = "Successfully retrieved current status";
    readFile('storage/sessions/md_'+res.locals.sessionId+'/creds.json', function( err, data )
    {
        if(err)
        {
           response(res, 403, true, 'Session not created', { status: "connecting", isSession:false })
        }else{
           const states = ['connecting', 'connected', 'disconnecting', 'disconnected']

           const session = getSession(res.locals.sessionId)
           let state = states[session.ws.readyState]

           state =
           state === 'connected' && typeof (session.isLegacy ? session.state.legacy.user : session.user) !== 'undefined'
           ? 'authenticated'
           : state

           let getWpData = readFileSync('storage/sessions/md_'+res.locals.sessionId+'/creds.json');
           let whatsappInfo = JSON.parse(getWpData);

           response(res, 200, true, message, { status: state,isSession:true,wpInfo: whatsappInfo.me })
        }
    });
}

const create = async (req, res) => {
    const { id, isLegacy, domain } = req.body
    if (isSessionExists(id)) {
        return response(res, 409, false, 'Session already exists, please use another id.')
    }
    // BYPASSED LICENSE CHECK FOR DEVELOPMENT
    try {
        xswpNodeSocketSet(id, isLegacy, domain, res);
    } catch (error) {
        response(res, 500, false, 'Unable to create QR code: ' + error.message);
    }
}

const initSystemTerminal = async (req, res = null) => {
    // BYPASSED LICENSE CHECK FOR DEVELOPMENT
    try{
        // License check bypassed for development
        response(res, 200, true, 'Software license verified (bypassed for development).')
    }catch(err){
        response(res, 400, false, "Node stream error:: CODE:: "+err)
    }
}

const del = async (req, res) => {
    const { id } = req.params
    const session = getSession(id)

    try {
        await session.logout()
    } catch {

    } finally {
        deleteSession(id, session.isLegacy)
    }

    response(res, 200, true, 'The session has been successfully deleted.')
}

const licenseCheck = async(req, id, isLegacy = false, res = null) => {
    // This function is kept for compatibility but does nothing
    return true;
}

export { getxscode, status, create, del, licenseCheck, initSystemTerminal }
